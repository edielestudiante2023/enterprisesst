<?php

namespace App\Controllers;

use Config\Database;
use CodeIgniter\Controller;

/**
 * Controlador para que el CLIENTE vea sus documentos SST aprobados
 * Solo lectura - sin edicion, sin gestion
 * Clientes solo ven documentos aprobados/firmados (con PDF o enlace listo)
 */
class ClienteDocumentosSstController extends Controller
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * Retorna los estados visibles segun el rol del usuario
     * Cliente: solo aprobado/firmado (tienen PDF listo o son soportes)
     * Admin/Consultant: todos los estados
     */
    protected function getEstadosVisibles(): array
    {
        $role = session()->get('role');
        if ($role === 'client') {
            return ['aprobado', 'firmado'];
        }
        return ['borrador', 'generado', 'aprobado', 'firmado', 'pendiente_firma'];
    }

    /**
     * Lista los documentos SST del cliente organizados por carpeta/estandar
     */
    public function index($idClienteParam = null)
    {
        $session = session();
        $role = $session->get('role');
        if ($idClienteParam && in_array($role, ['consultant', 'admin'])) {
            $idCliente = $idClienteParam;
        } else {
            $idCliente = $session->get('id_cliente') ?? $session->get('user_id');
        }

        if (!$idCliente) {
            return redirect()->to('/login')->with('error', 'Debe iniciar sesion');
        }

        // Obtener datos del cliente
        $cliente = $this->db->table('tbl_clientes')
            ->where('id_cliente', $idCliente)
            ->get()
            ->getRowArray();

        if (!$cliente) {
            return redirect()->to('/dashboardclient')->with('error', 'Cliente no encontrado');
        }

        // Obtener carpeta raiz del cliente
        $carpetaRaiz = $this->db->table('tbl_doc_carpetas')
            ->where('id_cliente', $idCliente)
            ->where('id_carpeta_padre IS NULL')
            ->where('visible', 1)
            ->get()
            ->getRowArray();

        // Obtener carpetas PHVA (hijos de la raiz)
        $carpetasPHVA = [];
        if ($carpetaRaiz) {
            $carpetasPHVA = $this->db->table('tbl_doc_carpetas')
                ->where('id_cliente', $idCliente)
                ->where('id_carpeta_padre', $carpetaRaiz['id_carpeta'])
                ->where('visible', 1)
                ->orderBy('orden', 'ASC')
                ->get()
                ->getResultArray();
        }

        // Construir arbol de carpetas con conteo de documentos
        $arbolCarpetas = [];
        foreach ($carpetasPHVA as $phva) {
            $arbolCarpetas[] = $this->construirArbolConDocumentos($phva, $idCliente);
        }

        // Obtener documentos del cliente filtrados por rol
        $todosDocumentos = $this->db->table('tbl_documentos_sst')
            ->where('id_cliente', $idCliente)
            ->whereIn('estado', $this->getEstadosVisibles())
            ->orderBy('updated_at', 'DESC')
            ->get()
            ->getResultArray();

        // Agregar archivo firmado a cada documento
        foreach ($todosDocumentos as &$doc) {
            $doc['archivo_firmado'] = $this->obtenerArchivoFirmado($doc['id_documento']);
        }

        // Total de documentos del cliente
        $totalDocumentos = count($todosDocumentos);

        $data = [
            'titulo' => 'Mis Documentos SST',
            'cliente' => $cliente,
            'arbolCarpetas' => $arbolCarpetas,
            'totalDocumentos' => $totalDocumentos,
            'todosDocumentos' => $todosDocumentos, // Lista completa de documentos
            'carpetaActual' => null
        ];

        return view('client/documentos_sst/index', $data);
    }

    /**
     * Muestra los documentos de una carpeta especifica
     */
    public function carpeta(int $idCarpeta)
    {
        $session = session();
        $role = $session->get('role');

        // Obtener carpeta primero para resolver el cliente desde ella
        $carpeta = $this->db->table('tbl_doc_carpetas')
            ->where('id_carpeta', $idCarpeta)
            ->get()
            ->getRowArray();

        if (!$carpeta) {
            return redirect()->to('/client/mis-documentos-sst')->with('error', 'Carpeta no encontrada');
        }

        // Resolver id_cliente desde la carpeta (no desde sesion)
        $idCliente = (int) $carpeta['id_cliente'];

        // Verificar autorizacion: clientes solo ven sus propias carpetas
        if ($role === 'client') {
            $idClienteSesion = $session->get('id_cliente') ?? $session->get('user_id');
            if ((int)$idClienteSesion !== $idCliente) {
                return redirect()->to('/client/mis-documentos-sst')->with('error', 'No tiene acceso a esta carpeta');
            }
        }

        // Obtener datos del cliente
        $cliente = $this->db->table('tbl_clientes')
            ->where('id_cliente', $idCliente)
            ->get()
            ->getRowArray();

        // Obtener ruta de navegacion (breadcrumb)
        $ruta = $this->obtenerRutaCarpeta($idCarpeta);

        // Obtener subcarpetas
        $subcarpetas = $this->db->table('tbl_doc_carpetas')
            ->where('id_cliente', $idCliente)
            ->where('id_carpeta_padre', $idCarpeta)
            ->where('visible', 1)
            ->orderBy('orden', 'ASC')
            ->get()
            ->getResultArray();

        // Agregar conteo de documentos a cada subcarpeta
        foreach ($subcarpetas as &$sub) {
            $sub['total_docs'] = $this->contarDocumentosEnCarpeta($sub['id_carpeta'], $idCliente);
        }

        // Detectar tipo de carpeta para obtener soportes específicos
        $tipoCarpetaFases = $this->determinarTipoCarpetaFases($carpeta);

        // Obtener documentos SST según el tipo de carpeta (igual que el consultor)
        $documentosSSTAprobados = $this->obtenerDocumentosSSTCarpeta($idCliente, $tipoCarpetaFases);

        // Obtener documentos antiguos por mapeo (compatibilidad)
        $documentos = $this->obtenerDocumentosCarpeta($idCarpeta, $idCliente);

        $data = [
            'titulo' => $carpeta['nombre'] . ' - Mis Documentos SST',
            'cliente' => $cliente,
            'carpeta' => $carpeta,
            'ruta' => $ruta,
            'subcarpetas' => $subcarpetas,
            'documentos' => $documentos,
            'tipoCarpetaFases' => $tipoCarpetaFases,
            'documentosSSTAprobados' => $documentosSSTAprobados,
            'idCliente' => $idCliente
        ];

        return view('client/documentos_sst/carpeta', $data);
    }

    /**
     * Obtiene documentos SST según el tipo de carpeta (igual que el consultor)
     */
    protected function obtenerDocumentosSSTCarpeta(int $idCliente, ?string $tipoCarpetaFases): array
    {
        if (!$tipoCarpetaFases) {
            return [];
        }

        $queryDocs = $this->db->table('tbl_documentos_sst')
            ->where('id_cliente', $idCliente)
            ->whereIn('estado', $this->getEstadosVisibles());

        // Mapeo de tipo de carpeta a tipo_documento
        $filtros = [
            'archivo_documental' => null, // Mostrar TODOS (usar array_key_exists)
            'responsabilidades_sgsst' => ['responsabilidades_rep_legal_sgsst', 'responsabilidades_responsable_sgsst', 'responsabilidades_trabajadores_sgsst'],
            'presupuesto_sst' => ['presupuesto_sst'],
            'afiliacion_srl' => ['planilla_afiliacion_srl'],
            'responsables_sst' => ['asignacion_responsable_sgsst'],
            'identificacion_alto_riesgo' => ['identificacion_alto_riesgo', 'listado_trabajadores_alto_riesgo'],
            'conformacion_copasst' => ['soporte_conformacion_copasst', 'acta_constitucion_copasst'],
            'capacitacion_copasst' => ['soporte_capacitacion_copasst'],
            'comite_convivencia' => ['soporte_comite_convivencia', 'manual_convivencia_laboral', 'acta_constitucion_cocolab', 'acta_recomposicion_cocolab', 'politica_acoso_laboral'],
            'capacitacion_sst' => ['programa_capacitacion'],
            'induccion_reinduccion' => ['programa_induccion_reinduccion'],
            'responsables_curso_50h' => ['soporte_curso_50h'],
            'reglamento_hsi' => ['reglamento_hsi'],
            'politica_sst' => ['politica_sst_general'],
            'evaluacion_prioridades' => ['soporte_evaluacion_prioridades'],
            'plan_trabajo_anual' => ['soporte_plan_trabajo_anual', 'soporte_plan_objetivos'],
            'plan_objetivos_metas' => ['soporte_plan_objetivos', 'plan_objetivos_metas'],
            'documentos_externos' => ['soporte_documento_externo'],
            'rendicion_desempeno' => ['soporte_rendicion_desempeno'],
            'matriz_legal' => ['procedimiento_matriz_legal', 'soporte_matriz_legal'],
            'mecanismos_comunicacion_sgsst' => ['mecanismos_comunicacion_sgsst'],
            'diagnostico_condiciones_salud' => ['soporte_diagnostico_salud'],
            'promocion_prevencion_salud' => ['programa_promocion_prevencion_salud'],
            'informacion_medico_perfiles' => ['soporte_perfiles_medico'],
            'evaluaciones_medicas' => ['soporte_evaluaciones_medicas'],
            'custodia_historias_clinicas' => ['soporte_custodia_hc'],
            'agua_servicios_sanitarios' => ['soporte_agua_servicios'],
            'eliminacion_residuos' => ['soporte_eliminacion_residuos'],
            'mediciones_ambientales' => ['soporte_mediciones_ambientales'],
            'medidas_prevencion_control' => ['soporte_medidas_prevencion_control'],
            'verificacion_medidas_prevencion' => ['soporte_verificacion_medidas'],
            'entrega_epp' => ['soporte_entrega_epp'],
            'plan_emergencias' => ['soporte_plan_emergencias'],
            'brigada_emergencias' => ['soporte_brigada_emergencias', 'acta_constitucion_brigada'],
            'indicadores_sgsst' => ['ficha_tecnica_ind_24', 'ficha_tecnica_ind_32'],
            'revision_direccion' => ['soporte_revision_direccion'],
            'planificacion_auditorias_copasst' => ['soporte_planificacion_auditoria'],
        ];

        if (array_key_exists($tipoCarpetaFases, $filtros)) {
            if ($filtros[$tipoCarpetaFases] !== null) {
                $queryDocs->whereIn('tipo_documento', $filtros[$tipoCarpetaFases]);
            }
            // null = mostrar todos (archivo_documental)
        } else {
            return [];
        }

        $documentosSSTAprobados = $queryDocs
            ->orderBy('anio', 'DESC')
            ->orderBy('updated_at', 'DESC')
            ->get()
            ->getResultArray();

        // Agregar información adicional a cada documento
        foreach ($documentosSSTAprobados as &$docSST) {
            // Conteo de firmas
            $firmaStats = $this->db->table('tbl_doc_firma_solicitudes')
                ->select("COUNT(*) as total, SUM(CASE WHEN estado = 'firmado' THEN 1 ELSE 0 END) as firmadas")
                ->where('id_documento', $docSST['id_documento'])
                ->get()
                ->getRowArray();
            $docSST['firmas_total'] = (int)($firmaStats['total'] ?? 0);
            $docSST['firmas_firmadas'] = (int)($firmaStats['firmadas'] ?? 0);

            // Versiones
            $versiones = $this->db->table('tbl_doc_versiones_sst')
                ->select('id_version, version_texto, estado, archivo_pdf')
                ->where('id_documento', $docSST['id_documento'])
                ->orderBy('id_version', 'DESC')
                ->get()
                ->getResultArray();

            $docSST['versiones'] = $versiones;
            $docSST['version_texto'] = !empty($versiones) ? $versiones[0]['version_texto'] : ($docSST['version'] . '.0');

            // Archivo PDF de versión vigente
            $versionVigente = array_filter($versiones, fn($v) => $v['estado'] === 'vigente');
            $versionVigente = reset($versionVigente);
            $docSST['archivo_pdf'] = $versionVigente['archivo_pdf'] ?? null;

            // URL externa si existe
            $docSST['url_externa'] = $docSST['url_externa'] ?? null;
        }

        return $documentosSSTAprobados;
    }

    /**
     * Determina el tipo de carpeta para obtener soportes específicos
     */
    protected function determinarTipoCarpetaFases(array $carpeta): ?string
    {
        $nombre = strtolower($carpeta['nombre'] ?? '');
        $codigo = strtolower($carpeta['codigo'] ?? '');

        // Mapeo por código de estándar
        $mapaCodigos = [
            '1.1.1' => 'responsables_sst',
            '1.1.2' => 'responsabilidades_sgsst',
            '1.1.3' => 'presupuesto_sst',
            '1.1.4' => 'afiliacion_srl',
            '1.1.5' => 'identificacion_alto_riesgo',
            '1.1.6' => 'conformacion_copasst',
            '1.1.7' => 'capacitacion_copasst',
            '1.1.8' => 'comite_convivencia',
            '1.2.1' => 'capacitacion_sst',
            '1.2.2' => 'induccion_reinduccion',
            '1.2.3' => 'responsables_curso_50h',
            '1.2.4' => 'reglamento_hsi',
            '2.1.1' => 'politica_sst',
            '2.3.1' => 'evaluacion_prioridades',
            '2.4.1' => 'plan_trabajo_anual',
            '2.5.1' => 'archivo_documental',
            '2.5.1.1' => 'documentos_externos',
            '2.6.1' => 'rendicion_desempeno',
            '2.7.1' => 'matriz_legal',
            '2.8.1' => 'mecanismos_comunicacion_sgsst',
            '3.1.1' => 'diagnostico_condiciones_salud',
            '3.1.2' => 'promocion_prevencion_salud',
            '3.1.3' => 'informacion_medico_perfiles',
            '3.1.4' => 'evaluaciones_medicas',
            '3.1.5' => 'custodia_historias_clinicas',
            '3.1.8' => 'agua_servicios_sanitarios',
            '3.1.9' => 'eliminacion_residuos',
            '4.1.4' => 'mediciones_ambientales',
            '4.2.1' => 'medidas_prevencion_control',
            '4.2.2' => 'verificacion_medidas_prevencion',
            '4.2.6' => 'entrega_epp',
            '5.1.1' => 'plan_emergencias',
            '5.1.2' => 'brigada_emergencias',
            '6.1.1' => 'indicadores_sgsst',
            '6.1.3' => 'revision_direccion',
            '6.1.4' => 'planificacion_auditorias_copasst',
        ];

        if (isset($mapaCodigos[$codigo])) {
            return $mapaCodigos[$codigo];
        }

        // Búsqueda por nombre si no hay código exacto
        if (strpos($nombre, 'capacitaci') !== false) return 'capacitacion_sst';
        if (strpos($nombre, 'responsabilidades') !== false) return 'responsabilidades_sgsst';
        if (strpos($nombre, 'presupuesto') !== false || strpos($nombre, 'recursos') !== false) return 'presupuesto_sst';
        if (strpos($nombre, 'afiliacion') !== false) return 'afiliacion_srl';
        if (strpos($nombre, 'copasst') !== false) return 'conformacion_copasst';
        if (strpos($nombre, 'convivencia') !== false) return 'comite_convivencia';
        if (strpos($nombre, 'archivo') !== false) return 'archivo_documental';

        return null;
    }

    /**
     * Construye el arbol de carpetas con conteo de documentos
     */
    protected function construirArbolConDocumentos(array $carpeta, int $idCliente): array
    {
        $hijos = $this->db->table('tbl_doc_carpetas')
            ->where('id_cliente', $idCliente)
            ->where('id_carpeta_padre', $carpeta['id_carpeta'])
            ->where('visible', 1)
            ->orderBy('orden', 'ASC')
            ->get()
            ->getResultArray();

        $carpeta['hijos'] = [];
        $carpeta['total_docs'] = $this->contarDocumentosEnCarpeta($carpeta['id_carpeta'], $idCliente);

        foreach ($hijos as $hijo) {
            $carpeta['hijos'][] = $this->construirArbolConDocumentos($hijo, $idCliente);
        }

        return $carpeta;
    }

    /**
     * Cuenta documentos en una carpeta (usando el nuevo sistema por tipo)
     */
    protected function contarDocumentosEnCarpeta(int $idCarpeta, int $idCliente): int
    {
        $carpeta = $this->db->table('tbl_doc_carpetas')
            ->where('id_carpeta', $idCarpeta)
            ->get()
            ->getRowArray();

        $total = 0;

        // Usar el nuevo sistema de detección de tipo de carpeta
        $tipoCarpetaFases = $this->determinarTipoCarpetaFases($carpeta);
        if ($tipoCarpetaFases) {
            $total = count($this->obtenerDocumentosSSTCarpeta($idCliente, $tipoCarpetaFases));
        }

        // Contar recursivamente en subcarpetas
        $subcarpetas = $this->db->table('tbl_doc_carpetas')
            ->where('id_cliente', $idCliente)
            ->where('id_carpeta_padre', $idCarpeta)
            ->where('visible', 1)
            ->get()
            ->getResultArray();

        foreach ($subcarpetas as $sub) {
            $total += $this->contarDocumentosEnCarpeta($sub['id_carpeta'], $idCliente);
        }

        return $total;
    }

    /**
     * Obtiene documentos de una carpeta especifica
     */
    protected function obtenerDocumentosCarpeta(int $idCarpeta, int $idCliente): array
    {
        $carpeta = $this->db->table('tbl_doc_carpetas')
            ->where('id_carpeta', $idCarpeta)
            ->get()
            ->getRowArray();

        $codigoCarpeta = $carpeta['codigo'] ?? '';

        // Buscar plantillas mapeadas
        $plantillas = $this->db->table('tbl_doc_plantilla_carpeta')
            ->where('codigo_carpeta', $codigoCarpeta)
            ->get()
            ->getResultArray();

        $documentos = [];

        foreach ($plantillas as $p) {
            $tipoDoc = $this->mapearPlantillaATipoDocumento($p['codigo_plantilla']);
            if ($tipoDoc) {
                $docs = $this->db->table('tbl_documentos_sst')
                    ->where('id_cliente', $idCliente)
                    ->where('tipo_documento', $tipoDoc)
                    ->whereIn('estado', $this->getEstadosVisibles())
                    ->orderBy('anio', 'DESC')
                    ->get()
                    ->getResultArray();

                foreach ($docs as $d) {
                    // Obtener enlace del archivo firmado (escaneado) si existe
                    $d['archivo_firmado'] = $this->obtenerArchivoFirmado($d['id_documento']);
                    $documentos[] = $d;
                }
            }
        }

        return $documentos;
    }

    /**
     * Obtiene el enlace del archivo firmado/escaneado si existe
     * Prioriza: tbl_doc_versiones_sst.archivo_pdf > tbl_reporte.enlace
     */
    protected function obtenerArchivoFirmado(int $idDocumento): ?string
    {
        // Primero buscar en versiones SST (más específico)
        $version = $this->db->table('tbl_doc_versiones_sst')
            ->select('archivo_pdf')
            ->where('id_documento', $idDocumento)
            ->where('estado', 'vigente')
            ->where('archivo_pdf IS NOT NULL')
            ->where('archivo_pdf !=', '')
            ->get()
            ->getRowArray();

        if ($version && !empty($version['archivo_pdf'])) {
            return $version['archivo_pdf'];
        }

        return null;
    }

    /**
     * Mapea codigo de plantilla a tipo_documento
     */
    protected function mapearPlantillaATipoDocumento(string $codigoPlantilla): ?string
    {
        $mapa = [
            // 1.2.1 Programa de Capacitación
            'PRG-CAP' => 'programa_capacitacion',
            // 1.1.1 Asignación del Responsable del SG-SST
            'ASG-RES' => 'asignacion_responsable_sgsst',
            // 1.1.2 Responsabilidades en el SG-SST (3 documentos)
            'RES-REP' => 'responsabilidades_rep_legal_sgsst',
            'RES-SST' => 'responsabilidades_responsable_sgsst',
            'RES-TRA' => 'responsabilidades_trabajadores_sgsst',
            // 1.1.3 Presupuesto SST
            'FT-SST-001' => 'presupuesto_sst',
            'PRE-SST' => 'presupuesto_sst',
            // 1.1.4 Afiliación al Sistema de Riesgos Laborales
            'AFL-SRL' => 'planilla_afiliacion_srl',
            // 1.1.5 Identificación de Trabajadores de Alto Riesgo
            'PR-SST-AR' => 'identificacion_alto_riesgo',
            // 1.1.6 Conformación COPASST / Vigía
            'COPASST' => 'soporte_conformacion_copasst',
            // 1.1.8 Comité de Convivencia Laboral / Manual de Convivencia
            'COCOLAB' => 'soporte_comite_convivencia',
            'MAN-CVL' => 'manual_convivencia_laboral',
            // 1.2.3 Responsables con curso 50 horas
            'CURSO-50H' => 'soporte_curso_50h',
            // 2.3.1 Evaluación e identificación de prioridades
            'EVAL-PRIO' => 'soporte_evaluacion_prioridades',
            // 2.4.1 Plan objetivos, metas
            'PLAN-OBJ' => 'soporte_plan_objetivos',
            // 2.5.1 Archivo/retención documental
            'ARCHIVO' => 'archivo_documental',
            // 2.6.1 Rendición sobre el desempeño
            'REND-DES' => 'soporte_rendicion_desempeno',
            // 3.1.1 Diagnóstico condiciones de salud
            'DIAG-SAL' => 'soporte_diagnostico_salud',
            // 3.1.2 Programa de Promoción y Prevención en Salud
            'PRG-PPS' => 'programa_promocion_prevencion_salud',
            // 3.1.3 Información médico perfiles
            'PERF-MED' => 'soporte_perfiles_medico',
            // 3.1.4 Evaluaciones médicas ocupacionales
            'EVAL-MED' => 'soporte_evaluaciones_medicas',
            // 3.1.5 Custodia historias clínicas
            'CUST-HC' => 'soporte_custodia_hc',
            // 3.1.8 Agua potable, servicios sanitarios
            'AGUA-SAN' => 'soporte_agua_servicios',
            // 3.1.9 Eliminación residuos
            'ELIM-RES' => 'soporte_eliminacion_residuos',
            // 4.1.4 Mediciones ambientales
            'MED-AMB' => 'soporte_mediciones_ambientales',
            // 4.2.1 Medidas de prevención y control
            'MED-PREV' => 'soporte_medidas_prevencion_control',
            // 4.2.2 Verificación medidas prevención
            'VER-MED' => 'soporte_verificacion_medidas',
            // 4.2.6 Entrega de EPP
            'ENT-EPP' => 'soporte_entrega_epp',
            // 5.1.1 Plan de emergencias
            'PLAN-EMER' => 'soporte_plan_emergencias',
            // 5.1.2 Brigada de emergencias
            'BRIG-EMER' => 'soporte_brigada_emergencias',
            // 6.1.3 Revisión por la dirección
            'REV-DIR' => 'soporte_revision_direccion',
            // 6.1.4 Planificación auditorías COPASST
            'AUD-COP' => 'soporte_planificacion_auditoria',
            // 2.7.1 Procedimiento Matriz de Requisitos Legales
            'PRC-MRL' => 'procedimiento_matriz_legal',
            // 2.8.1 Mecanismos de Comunicación, Auto Reporte
            'MEC-COM' => 'mecanismos_comunicacion_sgsst',
        ];

        return $mapa[$codigoPlantilla] ?? null;
    }

    /**
     * Obtiene la ruta de navegacion (breadcrumb) de una carpeta
     */
    protected function obtenerRutaCarpeta(int $idCarpeta): array
    {
        $ruta = [];
        $actual = $idCarpeta;

        while ($actual) {
            $carpeta = $this->db->table('tbl_doc_carpetas')
                ->where('id_carpeta', $actual)
                ->get()
                ->getRowArray();

            if ($carpeta) {
                array_unshift($ruta, $carpeta);
                $actual = $carpeta['id_carpeta_padre'];
            } else {
                break;
            }
        }

        return $ruta;
    }

    // NOTA: Metodos de aprobacion eliminados - Se usa PDF con firma electronica en su lugar
}
