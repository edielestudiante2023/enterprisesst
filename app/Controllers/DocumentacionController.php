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

        // Para procedimientos_seguridad (4.2.3), obtener fasesInfo de cada programa implementado
        $programasFasesInfo = [];
        if ($tipoCarpetaFases === 'procedimientos_seguridad') {
            $fasesService = new \App\Services\FasesDocumentoService();
            $programasImplementados = [
                'pve_riesgo_biomecanico' => 'PVE Riesgo Biomecánico',
                'pve_riesgo_psicosocial' => 'PVE Riesgo Psicosocial',
            ];
            foreach ($programasImplementados as $tipoProg => $nombreProg) {
                $programasFasesInfo[$tipoProg] = [
                    'nombre' => $nombreProg,
                    'fases' => $fasesService->getResumenFases($cliente['id_cliente'], $tipoProg)
                ];
            }
        }

        if ($tipoCarpetaFases) {
            $fasesService = $fasesService ?? new \App\Services\FasesDocumentoService();
            if ($tipoCarpetaFases !== 'procedimientos_seguridad') {
                $fasesInfo = $fasesService->getResumenFases($cliente['id_cliente'], $tipoCarpetaFases);
            }

            // Verificar si ya existe un documento generado para esta carpeta
            $mapaTipoDocumento = [
                'capacitacion_sst' => 'programa_capacitacion',
                'responsables_sst' => 'asignacion_responsable_sgsst',
                'promocion_prevencion_salud' => 'programa_promocion_prevencion_salud',
                'plan_objetivos_metas' => 'plan_objetivos_metas',
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

        // Obtener contexto del cliente para nivel de estándares (necesario para 1.1.2 y 2.1.1)
        $contextoCliente = null;
        if ($tipoCarpetaFases === 'responsabilidades_sgsst' || $tipoCarpetaFases === 'politicas_2_1_1') {
            $db = \Config\Database::connect();
            $contextoCliente = $db->table('tbl_cliente_contexto_sst')
                ->where('id_cliente', $cliente['id_cliente'])
                ->get()
                ->getRowArray();
        }

        // Obtener documentos SST aprobados para mostrar en tabla
        $documentosSSTAprobados = [];
        if (in_array($tipoCarpetaFases, ['capacitacion_sst', 'responsables_sst', 'responsabilidades_sgsst', 'archivo_documental', 'presupuesto_sst', 'afiliacion_srl', 'verificacion_medidas_prevencion', 'planificacion_auditorias_copasst', 'entrega_epp', 'plan_emergencias', 'brigada_emergencias', 'revision_direccion', 'agua_servicios_sanitarios', 'eliminacion_residuos', 'mediciones_ambientales', 'medidas_prevencion_control', 'diagnostico_condiciones_salud', 'informacion_medico_perfiles', 'evaluaciones_medicas', 'custodia_historias_clinicas', 'responsables_curso_50h', 'evaluacion_prioridades', 'plan_objetivos_metas', 'rendicion_desempeno', 'conformacion_copasst', 'comite_convivencia', 'manual_convivencia_1_1_8', 'promocion_prevencion_salud', 'induccion_reinduccion', 'matriz_legal', 'capacitacion_copasst', 'politicas_2_1_1', 'mecanismos_comunicacion_sgsst', 'adquisiciones_sst', 'evaluacion_proveedores', 'evaluacion_impacto_cambios', 'estilos_vida_saludable', 'reporte_accidentes_trabajo', 'investigacion_incidentes', 'procedimientos_seguridad', 'mantenimiento_periodico', 'identificacion_sustancias_cancerigenas', 'metodologia_identificacion_peligros'])) {
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
            } elseif ($tipoCarpetaFases === 'politicas_2_1_1') {
                // 2.1.1: Buscar las 6 políticas de SST
                $queryDocs->whereIn('tipo_documento', [
                    'politica_sst_general',
                    'politica_alcohol_drogas',
                    'politica_acoso_laboral',
                    'politica_violencias_genero',
                    'politica_discriminacion',
                    'politica_prevencion_emergencias'
                ]);
            } elseif ($tipoCarpetaFases === 'presupuesto_sst') {
                // 1.1.3: Presupuesto SST
                $queryDocs->where('tipo_documento', 'presupuesto_sst');
            } elseif ($tipoCarpetaFases === 'afiliacion_srl') {
                // 1.1.4: Afiliación al Sistema General de Riesgos Laborales
                $queryDocs->where('tipo_documento', 'planilla_afiliacion_srl');
            } elseif ($tipoCarpetaFases === 'verificacion_medidas_prevencion') {
                // 4.2.2: Verificación de aplicación de medidas de prevención y control
                $queryDocs->where('tipo_documento', 'soporte_verificacion_medidas');
            } elseif ($tipoCarpetaFases === 'planificacion_auditorias_copasst') {
                // 6.1.4: Planificación auditorías con el COPASST
                $queryDocs->where('tipo_documento', 'soporte_planificacion_auditoria');
            } elseif ($tipoCarpetaFases === 'entrega_epp') {
                // 4.2.6: Entrega de EPP
                $queryDocs->where('tipo_documento', 'soporte_entrega_epp');
            } elseif ($tipoCarpetaFases === 'plan_emergencias') {
                // 5.1.1: Plan de emergencias
                $queryDocs->where('tipo_documento', 'soporte_plan_emergencias');
            } elseif ($tipoCarpetaFases === 'brigada_emergencias') {
                // 5.1.2: Brigada de emergencias
                $queryDocs->where('tipo_documento', 'soporte_brigada_emergencias');
            } elseif ($tipoCarpetaFases === 'revision_direccion') {
                // 6.1.3: Revisión por la dirección
                $queryDocs->where('tipo_documento', 'soporte_revision_direccion');
            } elseif ($tipoCarpetaFases === 'agua_servicios_sanitarios') {
                // 3.1.8: Agua potable, servicios sanitarios
                $queryDocs->where('tipo_documento', 'soporte_agua_servicios');
            } elseif ($tipoCarpetaFases === 'eliminacion_residuos') {
                // 3.1.9: Eliminación de residuos
                $queryDocs->where('tipo_documento', 'soporte_eliminacion_residuos');
            } elseif ($tipoCarpetaFases === 'mediciones_ambientales') {
                // 4.1.4: Mediciones ambientales
                $queryDocs->where('tipo_documento', 'soporte_mediciones_ambientales');
            } elseif ($tipoCarpetaFases === 'medidas_prevencion_control') {
                // 4.2.1: Medidas de prevención y control
                $queryDocs->where('tipo_documento', 'soporte_medidas_prevencion_control');
            } elseif ($tipoCarpetaFases === 'diagnostico_condiciones_salud') {
                // 3.1.1: Procedimiento de Evaluaciones Médicas + Soportes
                $queryDocs->whereIn('tipo_documento', [
                    'procedimiento_evaluaciones_medicas',
                    'soporte_diagnostico_salud'
                ]);
            } elseif ($tipoCarpetaFases === 'informacion_medico_perfiles') {
                // 3.1.3: Información al médico perfiles de cargo
                $queryDocs->where('tipo_documento', 'soporte_perfiles_medico');
            } elseif ($tipoCarpetaFases === 'evaluaciones_medicas') {
                // 3.1.4: Evaluaciones médicas ocupacionales (Programa IA + Soportes)
                $queryDocs->whereIn('tipo_documento', [
                    'programa_evaluaciones_medicas_ocupacionales',
                    'soporte_evaluaciones_medicas'
                ]);
            } elseif ($tipoCarpetaFases === 'custodia_historias_clinicas') {
                // 3.1.5: Custodia historias clínicas
                $queryDocs->where('tipo_documento', 'soporte_custodia_hc');
            } elseif ($tipoCarpetaFases === 'responsables_curso_50h') {
                // 1.2.3: Responsables del SG-SST con curso virtual de 50 horas
                $queryDocs->where('tipo_documento', 'soporte_curso_50h');
            } elseif ($tipoCarpetaFases === 'evaluacion_prioridades') {
                // 2.3.1: Evaluación e identificación de prioridades
                $queryDocs->where('tipo_documento', 'soporte_evaluacion_prioridades');
            } elseif ($tipoCarpetaFases === 'plan_objetivos_metas') {
                // 2.2.1/2.4.1: Plan de Objetivos y Metas SG-SST
                $queryDocs->whereIn('tipo_documento', [
                    'plan_objetivos_metas',
                    'soporte_plan_objetivos'
                ]);
            } elseif ($tipoCarpetaFases === 'rendicion_desempeno') {
                // 2.6.1: Rendición sobre el desempeño
                $queryDocs->where('tipo_documento', 'soporte_rendicion_desempeno');
            } elseif ($tipoCarpetaFases === 'conformacion_copasst') {
                // 1.1.6: Conformación COPASST / Vigía
                $queryDocs->where('tipo_documento', 'soporte_conformacion_copasst');
            } elseif ($tipoCarpetaFases === 'comite_convivencia') {
                // 1.1.8: Conformación Comité de Convivencia (legacy)
                $queryDocs->where('tipo_documento', 'soporte_comite_convivencia');
            } elseif ($tipoCarpetaFases === 'manual_convivencia_1_1_8') {
                // 1.1.8: Manual de Convivencia + Soportes del Comité
                $queryDocs->whereIn('tipo_documento', [
                    'manual_convivencia_laboral',
                    'soporte_comite_convivencia'
                ]);
            } elseif ($tipoCarpetaFases === 'promocion_prevencion_salud') {
                // 3.1.2: Programa de Promoción y Prevención en Salud
                $queryDocs->where('tipo_documento', 'programa_promocion_prevencion_salud');
            } elseif ($tipoCarpetaFases === 'induccion_reinduccion') {
                // 1.2.2: Programa de Inducción y Reinducción en SG-SST
                $queryDocs->where('tipo_documento', 'programa_induccion_reinduccion');
            } elseif ($tipoCarpetaFases === 'matriz_legal') {
                // 2.7.1: Procedimiento de Matriz Legal
                $queryDocs->where('tipo_documento', 'procedimiento_matriz_legal');
            } elseif ($tipoCarpetaFases === 'capacitacion_copasst') {
                // 1.1.7: Capacitación COPASST
                $queryDocs->where('tipo_documento', 'soporte_capacitacion_copasst');
            } elseif ($tipoCarpetaFases === 'mecanismos_comunicacion_sgsst') {
                // 2.8.1: Mecanismos de Comunicación, Auto Reporte
                $queryDocs->where('tipo_documento', 'mecanismos_comunicacion_sgsst');
            } elseif ($tipoCarpetaFases === 'responsables_sst') {
                // 1.1.1: Asignación Responsable del SG-SST
                $queryDocs->where('tipo_documento', 'asignacion_responsable_sgsst');
            } elseif ($tipoCarpetaFases === 'adquisiciones_sst') {
                // 2.9.1: Procedimiento de Adquisiciones en SST
                $queryDocs->where('tipo_documento', 'procedimiento_adquisiciones');
            } elseif ($tipoCarpetaFases === 'evaluacion_proveedores') {
                // 2.10.1: Evaluación y Selección de Proveedores y Contratistas
                $queryDocs->where('tipo_documento', 'procedimiento_evaluacion_proveedores');
            } elseif ($tipoCarpetaFases === 'evaluacion_impacto_cambios') {
                // 2.11.1: Procedimiento de Gestion del Cambio
                $queryDocs->where('tipo_documento', 'procedimiento_gestion_cambio');
            } elseif ($tipoCarpetaFases === 'estilos_vida_saludable') {
                // 3.1.7: Programa de Estilos de Vida Saludable
                $queryDocs->where('tipo_documento', 'programa_estilos_vida_saludable');
            } elseif ($tipoCarpetaFases === 'reporte_accidentes_trabajo') {
                // 3.2.1: Procedimiento de Investigacion de Accidentes
                $queryDocs->where('tipo_documento', 'procedimiento_investigacion_accidentes');
            } elseif ($tipoCarpetaFases === 'investigacion_incidentes') {
                // 3.2.2: Investigacion de Incidentes, Accidentes y Enfermedades Laborales
                $queryDocs->where('tipo_documento', 'procedimiento_investigacion_incidentes');
            } elseif ($tipoCarpetaFases === 'metodologia_identificacion_peligros') {
                // 4.1.1: Metodologia Identificacion de Peligros y Valoracion de Riesgos
                $queryDocs->where('tipo_documento', 'metodologia_identificacion_peligros');
            } elseif ($tipoCarpetaFases === 'identificacion_sustancias_cancerigenas') {
                // 4.1.3: Identificacion de Sustancias Cancerigenas o con Toxicidad Aguda
                $queryDocs->where('tipo_documento', 'identificacion_sustancias_cancerigenas');
            } elseif ($tipoCarpetaFases === 'procedimientos_seguridad') {
                // 4.2.3: Programas de Vigilancia Epidemiologica / PVEs
                $queryDocs->whereIn('tipo_documento', [
                    'pve_riesgo_biomecanico',
                    'pve_riesgo_psicosocial'
                ]);
            } elseif ($tipoCarpetaFases === 'mantenimiento_periodico') {
                // 4.2.5: Mantenimiento Periodico
                $queryDocs->where('tipo_documento', 'programa_mantenimiento_periodico');
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

        // Soportes adicionales para carpetas con fases
        $soportesAdicionales = [];
        if ($tipoCarpetaFases === 'promocion_prevencion_salud') {
            // 3.1.2 Promoción y Prevención en Salud
            $db = $db ?? \Config\Database::connect();
            $soportesAdicionales = $db->table('tbl_documentos_sst')
                ->where('id_cliente', $cliente['id_cliente'])
                ->where('tipo_documento', 'soporte_pyp_salud')
                ->orderBy('created_at', 'DESC')
                ->get()
                ->getResultArray();
        } elseif ($tipoCarpetaFases === 'induccion_reinduccion') {
            // 1.2.2 Inducción y Reinducción en SG-SST
            $db = $db ?? \Config\Database::connect();
            $soportesAdicionales = $db->table('tbl_documentos_sst')
                ->where('id_cliente', $cliente['id_cliente'])
                ->where('tipo_documento', 'soporte_induccion')
                ->orderBy('created_at', 'DESC')
                ->get()
                ->getResultArray();
        } elseif ($tipoCarpetaFases === 'matriz_legal') {
            // 2.7.1 Matriz Legal
            $db = $db ?? \Config\Database::connect();
            $soportesAdicionales = $db->table('tbl_documentos_sst')
                ->where('id_cliente', $cliente['id_cliente'])
                ->where('tipo_documento', 'soporte_matriz_legal')
                ->orderBy('created_at', 'DESC')
                ->get()
                ->getResultArray();
        } elseif ($tipoCarpetaFases === 'mecanismos_comunicacion_sgsst') {
            // 2.8.1 Mecanismos de Comunicación, Auto Reporte
            $db = $db ?? \Config\Database::connect();
            $soportesAdicionales = $db->table('tbl_documentos_sst')
                ->where('id_cliente', $cliente['id_cliente'])
                ->where('tipo_documento', 'soporte_mecanismos_comunicacion')
                ->orderBy('created_at', 'DESC')
                ->get()
                ->getResultArray();
        } elseif ($tipoCarpetaFases === 'diagnostico_condiciones_salud') {
            // 3.1.1 Diagnóstico de Condiciones de Salud - Soportes adjuntos
            $db = $db ?? \Config\Database::connect();
            $soportesAdicionales = $db->table('tbl_documentos_sst')
                ->where('id_cliente', $cliente['id_cliente'])
                ->where('tipo_documento', 'soporte_diagnostico_salud')
                ->orderBy('created_at', 'DESC')
                ->get()
                ->getResultArray();
        } elseif ($tipoCarpetaFases === 'capacitacion_copasst') {
            // 1.1.7 Capacitación COPASST
            $db = $db ?? \Config\Database::connect();
            $soportesAdicionales = $db->table('tbl_documentos_sst')
                ->where('id_cliente', $cliente['id_cliente'])
                ->where('tipo_documento', 'soporte_capacitacion_copasst')
                ->orderBy('created_at', 'DESC')
                ->get()
                ->getResultArray();
        } elseif ($tipoCarpetaFases === 'estilos_vida_saludable') {
            // 3.1.7 Estilos de Vida Saludable
            $db = $db ?? \Config\Database::connect();
            $soportesAdicionales = $db->table('tbl_documentos_sst')
                ->where('id_cliente', $cliente['id_cliente'])
                ->where('tipo_documento', 'soporte_estilos_vida_saludable')
                ->orderBy('created_at', 'DESC')
                ->get()
                ->getResultArray();
        } elseif ($tipoCarpetaFases === 'evaluacion_impacto_cambios') {
            // 2.11.1 Evaluación del Impacto de Cambios / Gestión del Cambio
            $db = $db ?? \Config\Database::connect();
            $soportesAdicionales = $db->table('tbl_documentos_sst')
                ->where('id_cliente', $cliente['id_cliente'])
                ->where('tipo_documento', 'soporte_gestion_cambio')
                ->orderBy('created_at', 'DESC')
                ->get()
                ->getResultArray();
        } elseif ($tipoCarpetaFases === 'evaluaciones_medicas') {
            // 3.1.4 Evaluaciones Médicas Ocupacionales - Soportes adjuntos
            $db = $db ?? \Config\Database::connect();
            $soportesAdicionales = $db->table('tbl_documentos_sst')
                ->where('id_cliente', $cliente['id_cliente'])
                ->where('tipo_documento', 'soporte_evaluaciones_medicas')
                ->orderBy('created_at', 'DESC')
                ->get()
                ->getResultArray();
        } elseif ($tipoCarpetaFases === 'reporte_accidentes_trabajo') {
            // 3.2.1 Investigacion de Accidentes de Trabajo y Enfermedades Laborales
            $db = $db ?? \Config\Database::connect();
            $soportesAdicionales = $db->table('tbl_documentos_sst')
                ->where('id_cliente', $cliente['id_cliente'])
                ->where('tipo_documento', 'soporte_investigacion_accidentes')
                ->orderBy('created_at', 'DESC')
                ->get()
                ->getResultArray();
        } elseif ($tipoCarpetaFases === 'investigacion_incidentes') {
            // 3.2.2 Investigacion de Incidentes, Accidentes y Enfermedades Laborales
            $db = $db ?? \Config\Database::connect();
            $soportesAdicionales = $db->table('tbl_documentos_sst')
                ->where('id_cliente', $cliente['id_cliente'])
                ->where('tipo_documento', 'soporte_investigacion_incidentes')
                ->orderBy('created_at', 'DESC')
                ->get()
                ->getResultArray();
        } elseif ($tipoCarpetaFases === 'metodologia_identificacion_peligros') {
            // 4.1.1 Metodologia Identificacion de Peligros y Valoracion de Riesgos
            $db = $db ?? \Config\Database::connect();
            $soportesAdicionales = $db->table('tbl_documentos_sst')
                ->where('id_cliente', $cliente['id_cliente'])
                ->where('tipo_documento', 'soporte_metodologia_peligros')
                ->orderBy('created_at', 'DESC')
                ->get()
                ->getResultArray();
        } elseif ($tipoCarpetaFases === 'identificacion_sustancias_cancerigenas') {
            // 4.1.3 Identificacion de Sustancias Cancerigenas o con Toxicidad Aguda
            $db = $db ?? \Config\Database::connect();
            $soportesAdicionales = $db->table('tbl_documentos_sst')
                ->where('id_cliente', $cliente['id_cliente'])
                ->where('tipo_documento', 'soporte_sustancias_cancerigenas')
                ->orderBy('created_at', 'DESC')
                ->get()
                ->getResultArray();
        } elseif ($tipoCarpetaFases === 'procedimientos_seguridad') {
            // 4.2.3 Programas de Vigilancia Epidemiologica - Soportes PVE
            $db = $db ?? \Config\Database::connect();
            $soportesAdicionales = $db->table('tbl_documentos_sst')
                ->where('id_cliente', $cliente['id_cliente'])
                ->whereIn('tipo_documento', ['soporte_pve_biomecanico', 'soporte_pve_psicosocial'])
                ->orderBy('created_at', 'DESC')
                ->get()
                ->getResultArray();
        } elseif ($tipoCarpetaFases === 'mantenimiento_periodico') {
            // 4.2.5 Mantenimiento Periodico
            $db = $db ?? \Config\Database::connect();
            $soportesAdicionales = $db->table('tbl_documentos_sst')
                ->where('id_cliente', $cliente['id_cliente'])
                ->where('tipo_documento', 'soporte_mantenimiento_periodico')
                ->orderBy('created_at', 'DESC')
                ->get()
                ->getResultArray();
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
            'soportesAdicionales' => $soportesAdicionales,
            'contextoCliente' => $contextoCliente ?? null,
            'vistaContenido' => $vistaPath,
            'programasFasesInfo' => $programasFasesInfo ?? [],
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

        // ============================================
        // 1.1.7. Capacitación COPASST - Adjuntar soportes de capacitación
        // Permite adjuntar archivos o enlaces de capacitaciones realizadas al COPASST
        // ============================================
        if ($codigo === '1.1.7') {
            return 'capacitacion_copasst';
        }

        // 1.2.1. Programa Capacitacion PYP (Ciclo Planear)
        if ($codigo === '1.2.1' ||
            (strpos($nombre, 'programa') !== false && strpos($nombre, 'capacitaci') !== false)) {
            return 'capacitacion_sst';
        }

        // 1.2.2. Inducción y Reinducción en SG-SST
        if ($codigo === '1.2.2' ||
            strpos($nombre, 'induccion') !== false ||
            strpos($nombre, 'reinduccion') !== false) {
            return 'induccion_reinduccion';
        }

        // 2.2.1. Objetivos definidos, claros, medibles, cuantificables, con metas
        // 2.4.1. Plan que identifica objetivos, metas, responsabilidad, recursos
        if ($codigo === '2.2.1' ||
            $codigo === '2.4.1' ||
            (strpos($nombre, 'plan') !== false && strpos($nombre, 'objetivos') !== false) ||
            (strpos($nombre, 'objetivos') !== false && strpos($nombre, 'metas') !== false)) {
            return 'plan_objetivos_metas';
        }

        // 2.1.1. Políticas de Seguridad y Salud en el Trabajo (5 documentos)
        if ($codigo === '2.1.1' ||
            strpos($nombre, 'politica') !== false && strpos($nombre, 'seguridad') !== false ||
            strpos($nombre, 'politicas') !== false && strpos($nombre, 'sst') !== false) {
            return 'politicas_2_1_1';
        }

        // 1.2.3. Responsables del SG-SST con curso virtual de 50 horas (ANTES de 1.1.1 porque es más específico)
        if ($codigo === '1.2.3' ||
            strpos($nombre, 'curso') !== false && strpos($nombre, '50') !== false ||
            strpos($nombre, 'responsables') !== false && strpos($nombre, 'curso') !== false) {
            return 'responsables_curso_50h';
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

        // 4.2.2. Verificación de aplicación de medidas de prevención y control
        if ($codigo === '4.2.2' ||
            strpos($nombre, 'verificacion') !== false && strpos($nombre, 'medidas') !== false ||
            strpos($nombre, 'verificacion') !== false && strpos($nombre, 'prevencion') !== false) {
            return 'verificacion_medidas_prevencion';
        }

        // 6.1.4. Planificación auditorías con el COPASST
        if ($codigo === '6.1.4' ||
            strpos($nombre, 'planificacion') !== false && strpos($nombre, 'auditoria') !== false ||
            strpos($nombre, 'auditoria') !== false && strpos($nombre, 'copasst') !== false) {
            return 'planificacion_auditorias_copasst';
        }

        // 4.2.6. Entrega de EPP, verificación con contratistas y subcontratistas
        if ($codigo === '4.2.6' ||
            strpos($nombre, 'entrega') !== false && strpos($nombre, 'epp') !== false ||
            strpos($nombre, 'elementos') !== false && strpos($nombre, 'proteccion') !== false) {
            return 'entrega_epp';
        }

        // 5.1.1. Plan de Prevención, Preparación y respuesta ante emergencias
        if ($codigo === '5.1.1' ||
            strpos($nombre, 'plan') !== false && strpos($nombre, 'emergencia') !== false ||
            strpos($nombre, 'prevencion') !== false && strpos($nombre, 'preparacion') !== false) {
            return 'plan_emergencias';
        }

        // 5.1.2. Brigada de prevención conformada, capacitada y dotada
        if ($codigo === '5.1.2' ||
            strpos($nombre, 'brigada') !== false) {
            return 'brigada_emergencias';
        }

        // 6.1.3. Revisión anual de la alta dirección, resultados de auditoría
        if ($codigo === '6.1.3' ||
            strpos($nombre, 'revision') !== false && strpos($nombre, 'direccion') !== false ||
            strpos($nombre, 'revision') !== false && strpos($nombre, 'anual') !== false) {
            return 'revision_direccion';
        }

        // 3.1.7. Estilos de vida y entornos saludables
        if ($codigo === '3.1.7' ||
            strpos($nombre, 'estilos') !== false && strpos($nombre, 'vida') !== false ||
            strpos($nombre, 'entornos') !== false && strpos($nombre, 'saludables') !== false ||
            strpos($nombre, 'tabaquismo') !== false || strpos($nombre, 'farmacodependencia') !== false) {
            return 'estilos_vida_saludable';
        }

        // 3.1.8. Agua potable, servicios sanitarios y disposición de basuras
        if ($codigo === '3.1.8' ||
            strpos($nombre, 'agua') !== false && strpos($nombre, 'potable') !== false ||
            strpos($nombre, 'servicios') !== false && strpos($nombre, 'sanitarios') !== false) {
            return 'agua_servicios_sanitarios';
        }

        // 3.1.9. Eliminación adecuada de residuos sólidos, líquidos o gaseosos
        if ($codigo === '3.1.9' ||
            strpos($nombre, 'eliminacion') !== false && strpos($nombre, 'residuos') !== false ||
            strpos($nombre, 'residuos') !== false && strpos($nombre, 'solidos') !== false) {
            return 'eliminacion_residuos';
        }

        // 4.1.4. Realización mediciones ambientales, químicos, físicos y biológicos
        if ($codigo === '4.1.4' ||
            strpos($nombre, 'mediciones') !== false && strpos($nombre, 'ambientales') !== false ||
            strpos($nombre, 'mediciones') !== false && strpos($nombre, 'quimicos') !== false) {
            return 'mediciones_ambientales';
        }

        // 4.2.1. Implementación de medidas de prevención y control frente a peligros/riesgos
        if ($codigo === '4.2.1' ||
            strpos($nombre, 'implementacion') !== false && strpos($nombre, 'medidas') !== false ||
            strpos($nombre, 'medidas') !== false && strpos($nombre, 'control') !== false && strpos($nombre, 'peligros') !== false) {
            return 'medidas_prevencion_control';
        }

        // 3.1.1. Descripción sociodemográfica - Diagnóstico de Condiciones de Salud
        if ($codigo === '3.1.1' ||
            strpos($nombre, 'sociodemograf') !== false ||
            strpos($nombre, 'diagnostico') !== false && strpos($nombre, 'salud') !== false) {
            return 'diagnostico_condiciones_salud';
        }

        // 3.1.2. Programa de Promoción y Prevención en Salud
        if ($codigo === '3.1.2' ||
            strpos($nombre, 'promocion') !== false && strpos($nombre, 'prevencion') !== false ||
            strpos($nombre, 'promocion') !== false && strpos($nombre, 'salud') !== false) {
            return 'promocion_prevencion_salud';
        }

        // 3.1.3. Información al médico de los perfiles de cargo
        if ($codigo === '3.1.3' ||
            strpos($nombre, 'informacion') !== false && strpos($nombre, 'medico') !== false ||
            strpos($nombre, 'perfiles') !== false && strpos($nombre, 'cargo') !== false) {
            return 'informacion_medico_perfiles';
        }

        // 3.1.4. Realización de evaluaciones médicas ocupacionales
        if ($codigo === '3.1.4' ||
            strpos($nombre, 'evaluaciones') !== false && strpos($nombre, 'medicas') !== false ||
            strpos($nombre, 'examenes') !== false && strpos($nombre, 'medicos') !== false) {
            return 'evaluaciones_medicas';
        }

        // 3.1.5. Custodia de Historias Clínicas
        if ($codigo === '3.1.5' ||
            strpos($nombre, 'custodia') !== false && strpos($nombre, 'historias') !== false ||
            strpos($nombre, 'historias') !== false && strpos($nombre, 'clinicas') !== false) {
            return 'custodia_historias_clinicas';
        }

        // 2.3.1. Evaluación e identificación de prioridades
        if ($codigo === '2.3.1' ||
            strpos($nombre, 'evaluacion') !== false && strpos($nombre, 'prioridades') !== false ||
            strpos($nombre, 'identificacion') !== false && strpos($nombre, 'prioridades') !== false) {
            return 'evaluacion_prioridades';
        }

        // 2.6.1. Rendición sobre el desempeño
        if ($codigo === '2.6.1' ||
            strpos($nombre, 'rendicion') !== false && strpos($nombre, 'desempeno') !== false ||
            strpos($nombre, 'rendicion') !== false && strpos($nombre, 'cuentas') !== false) {
            return 'rendicion_desempeno';
        }

        // 2.7.1. Matriz de requisitos legales
        if ($codigo === '2.7.1' ||
            strpos($nombre, 'matriz') !== false && strpos($nombre, 'legal') !== false ||
            strpos($nombre, 'requisitos') !== false && strpos($nombre, 'legales') !== false) {
            return 'matriz_legal';
        }

        // 2.8.1. Mecanismos de comunicación, auto reporte en SG-SST
        if ($codigo === '2.8.1' ||
            strpos($nombre, 'mecanismos') !== false && strpos($nombre, 'comunicacion') !== false ||
            strpos($nombre, 'auto reporte') !== false ||
            strpos($nombre, 'autoreporte') !== false) {
            return 'mecanismos_comunicacion_sgsst';
        }

        // 1.1.6. Conformación COPASST / Vigía
        if ($codigo === '1.1.6' ||
            strpos($nombre, 'copasst') !== false ||
            strpos($nombre, 'vigia') !== false && strpos($nombre, 'seguridad') !== false) {
            return 'conformacion_copasst';
        }

        // 1.1.8. Conformación Comité de Convivencia Laboral / Manual de Convivencia
        if ($codigo === '1.1.8' ||
            strpos($nombre, 'convivencia') !== false ||
            strpos($nombre, 'comite') !== false && strpos($nombre, 'convivencia') !== false) {
            return 'manual_convivencia_1_1_8';
        }

        // ============================================
        // MÓDULO DE ACCIONES CORRECTIVAS (7.1.x)
        // ============================================

        // 7.1.1. Definición de acciones preventivas y correctivas con base en resultados del SG-SST
        if ($codigo === '7.1.1' ||
            strpos($nombre, 'acciones') !== false && strpos($nombre, 'resultados') !== false ||
            strpos($nombre, 'preventivas') !== false && strpos($nombre, 'correctivas') !== false && strpos($nombre, 'resultados') !== false) {
            return 'acciones_resultados_sgsst';
        }

        // 7.1.2. Acciones de mejora conforme a revisión de la alta dirección (efectividad medidas)
        if ($codigo === '7.1.2' ||
            strpos($nombre, 'acciones') !== false && strpos($nombre, 'mejora') !== false && strpos($nombre, 'revision') !== false ||
            strpos($nombre, 'efectividad') !== false && strpos($nombre, 'medidas') !== false) {
            return 'acciones_efectividad_medidas';
        }

        // 7.1.3. Acciones de mejora con base en investigaciones de accidentes de trabajo y enfermedades
        if ($codigo === '7.1.3' ||
            strpos($nombre, 'acciones') !== false && strpos($nombre, 'investigacion') !== false ||
            strpos($nombre, 'acciones') !== false && strpos($nombre, 'accidentes') !== false ||
            strpos($nombre, 'acciones') !== false && strpos($nombre, 'enfermedades') !== false) {
            return 'acciones_investigacion_atel';
        }

        // 7.1.4. Elaboración Plan de mejoramiento, implementación de medidas y acciones correctivas por autoridades y ARL
        if ($codigo === '7.1.4' ||
            strpos($nombre, 'plan') !== false && strpos($nombre, 'mejoramiento') !== false ||
            strpos($nombre, 'acciones') !== false && strpos($nombre, 'arl') !== false ||
            strpos($nombre, 'acciones') !== false && strpos($nombre, 'autoridades') !== false) {
            return 'acciones_arl_autoridades';
        }

        // 2.9.1. Identificación, evaluación para adquisición de productos y servicios en SST
        if ($codigo === '2.9.1' ||
            strpos($nombre, 'adquisicion') !== false && strpos($nombre, 'producto') !== false ||
            strpos($nombre, 'adquisicion') !== false && strpos($nombre, 'servicio') !== false ||
            strpos($nombre, 'adquisiciones') !== false && strpos($nombre, 'sst') !== false) {
            return 'adquisiciones_sst';
        }

        // 2.10.1. Evaluación y selección de proveedores y contratistas
        if ($codigo === '2.10.1' ||
            strpos($nombre, 'evaluacion') !== false && strpos($nombre, 'proveedores') !== false ||
            strpos($nombre, 'seleccion') !== false && strpos($nombre, 'proveedores') !== false ||
            strpos($nombre, 'seleccion') !== false && strpos($nombre, 'contratistas') !== false) {
            return 'evaluacion_proveedores';
        }

        // 2.11.1. Evaluación del impacto de cambios / Gestión del cambio
        if ($codigo === '2.11.1' ||
            strpos($nombre, 'gestion') !== false && strpos($nombre, 'cambio') !== false ||
            strpos($nombre, 'impacto') !== false && strpos($nombre, 'cambios') !== false ||
            strpos($nombre, 'evaluacion') !== false && strpos($nombre, 'impacto') !== false && strpos($nombre, 'cambios') !== false) {
            return 'evaluacion_impacto_cambios';
        }

        // 3.2.2. Investigación de incidentes, accidentes y enfermedades laborales (determinación de causas)
        // DEBE ir ANTES de 3.2.1 para que el código '3.2.2' se capture primero
        if ($codigo === '3.2.2' ||
            strpos($nombre, 'investigacion') !== false && strpos($nombre, 'incidentes') !== false ||
            strpos($nombre, 'causas') !== false && strpos($nombre, 'basicas') !== false ||
            strpos($nombre, 'causas') !== false && strpos($nombre, 'inmediatas') !== false) {
            return 'investigacion_incidentes';
        }

        // 4.1.1. Metodología para la identificación de peligros, evaluación y valoración de riesgos
        if ($codigo === '4.1.1' ||
            strpos($nombre, 'metodologia') !== false && strpos($nombre, 'peligros') !== false ||
            strpos($nombre, 'identificacion') !== false && strpos($nombre, 'peligros') !== false && strpos($nombre, 'riesgos') !== false ||
            strpos($nombre, 'valoracion') !== false && strpos($nombre, 'riesgos') !== false) {
            return 'metodologia_identificacion_peligros';
        }

        // 4.1.3. Identificación de sustancias catalogadas como cancerígenas o con toxicidad aguda
        if ($codigo === '4.1.3' ||
            strpos($nombre, 'sustancias') !== false && strpos($nombre, 'cancerigenas') !== false ||
            strpos($nombre, 'sustancias') !== false && strpos($nombre, 'toxicidad') !== false ||
            strpos($nombre, 'cancerigenas') !== false && strpos($nombre, 'toxicidad') !== false) {
            return 'identificacion_sustancias_cancerigenas';
        }

        // 3.2.1. Reporte e investigación de accidentes de trabajo y enfermedades laborales
        if ($codigo === '3.2.1' ||
            strpos($nombre, 'reporte') !== false && strpos($nombre, 'accidentes') !== false ||
            strpos($nombre, 'investigacion') !== false && strpos($nombre, 'accidentes') !== false) {
            return 'reporte_accidentes_trabajo';
        }

        // 4.2.3. Programas de vigilancia epidemiológica / PVE / Procedimientos de seguridad
        if ($codigo === '4.2.3' ||
            strpos($nombre, 'vigilancia') !== false && strpos($nombre, 'epidemiologica') !== false ||
            strpos($nombre, 'procedimientos') !== false && strpos($nombre, 'seguridad') !== false ||
            strpos($nombre, 'programas') !== false && strpos($nombre, 'seguridad') !== false && strpos($nombre, 'salud') !== false ||
            strpos($nombre, 'pve') !== false) {
            return 'procedimientos_seguridad';
        }

        // 4.2.5. Mantenimiento periódico de instalaciones, equipos, máquinas, herramientas
        if ($codigo === '4.2.5' ||
            strpos($nombre, 'mantenimiento') !== false && strpos($nombre, 'periodico') !== false ||
            strpos($nombre, 'mantenimiento') !== false && strpos($nombre, 'instalaciones') !== false ||
            strpos($nombre, 'mantenimiento') !== false && strpos($nombre, 'equipos') !== false) {
            return 'mantenimiento_periodico';
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
