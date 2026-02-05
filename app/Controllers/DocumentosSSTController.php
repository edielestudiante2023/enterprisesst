<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\ClienteContextoSstModel;
use App\Models\CronogcapacitacionModel;
use App\Models\IndicadorSSTModel;
use App\Models\ResponsableSSTModel;
use App\Services\CronogramaIAService;
use App\Services\PTAGeneratorService;
use App\Services\IADocumentacionService;
use App\Services\DocumentoConfigService;
use App\Services\DocumentoVersionService;
use App\Services\FirmanteService;
use App\Libraries\DocumentosSSTTypes\DocumentoSSTFactory;
use Config\Database;

/**
 * Controlador para visualizar y generar documentos del SG-SST
 */
class DocumentosSSTController extends BaseController
{
    protected $db;
    protected ClientModel $clienteModel;
    protected DocumentoConfigService $configService;
    protected DocumentoVersionService $versionService;
    protected FirmanteService $firmanteService;

    /**
     * @deprecated Esta constante está OBSOLETA. Usar DocumentoConfigService en su lugar.
     * Los tipos de documento ahora se obtienen de tbl_doc_tipo_configuracion.
     * Se mantiene temporalmente por compatibilidad con código legacy.
     */
    public const TIPOS_DOCUMENTO = [
        'programa_capacitacion' => [
            'nombre' => 'Programa de Capacitacion',
            'descripcion' => 'Documento formal del programa de capacitacion en SST',
            'flujo' => 'secciones_ia', // Usa editor de secciones con IA
            'secciones' => [
                ['numero' => 1, 'nombre' => 'Introduccion', 'key' => 'introduccion'],
                ['numero' => 2, 'nombre' => 'Objetivo General', 'key' => 'objetivo_general'],
                ['numero' => 3, 'nombre' => 'Objetivos Especificos', 'key' => 'objetivos_especificos'],
                ['numero' => 4, 'nombre' => 'Alcance', 'key' => 'alcance'],
                ['numero' => 5, 'nombre' => 'Marco Legal', 'key' => 'marco_legal'],
                ['numero' => 6, 'nombre' => 'Definiciones', 'key' => 'definiciones'],
                ['numero' => 7, 'nombre' => 'Responsabilidades', 'key' => 'responsabilidades'],
                ['numero' => 8, 'nombre' => 'Metodologia', 'key' => 'metodologia'],
                ['numero' => 9, 'nombre' => 'Cronograma de Capacitaciones', 'key' => 'cronograma'],
                ['numero' => 10, 'nombre' => 'Plan de Trabajo Anual', 'key' => 'plan_trabajo'],
                ['numero' => 11, 'nombre' => 'Indicadores', 'key' => 'indicadores'],
                ['numero' => 12, 'nombre' => 'Recursos', 'key' => 'recursos'],
                ['numero' => 13, 'nombre' => 'Evaluacion y Seguimiento', 'key' => 'evaluacion'],
            ]
        ],
        'procedimiento_control_documental' => [
            'nombre' => 'Procedimiento de Control Documental del SG-SST',
            'descripcion' => 'Establece las directrices para la elaboracion, revision, aprobacion, distribucion y conservacion de documentos del SG-SST',
            'flujo' => 'secciones_ia',
            'estandar' => '2.5.1',
            'firmantes' => ['representante_legal', 'responsable_sst'],
            'secciones' => [
                ['numero' => 1, 'nombre' => 'Objetivo', 'key' => 'objetivo'],
                ['numero' => 2, 'nombre' => 'Alcance', 'key' => 'alcance'],
                ['numero' => 3, 'nombre' => 'Definiciones', 'key' => 'definiciones'],
                ['numero' => 4, 'nombre' => 'Marco Normativo', 'key' => 'marco_normativo'],
                ['numero' => 5, 'nombre' => 'Responsabilidades', 'key' => 'responsabilidades'],
                ['numero' => 6, 'nombre' => 'Tipos de Documentos del SG-SST', 'key' => 'tipos_documentos'],
                ['numero' => 7, 'nombre' => 'Estructura y Codificacion', 'key' => 'codificacion'],
                ['numero' => 8, 'nombre' => 'Elaboracion de Documentos', 'key' => 'elaboracion'],
                ['numero' => 9, 'nombre' => 'Revision y Aprobacion', 'key' => 'revision_aprobacion'],
                ['numero' => 10, 'nombre' => 'Distribucion y Acceso', 'key' => 'distribucion'],
                ['numero' => 11, 'nombre' => 'Control de Cambios', 'key' => 'control_cambios'],
                ['numero' => 12, 'nombre' => 'Conservacion y Retencion', 'key' => 'conservacion'],
                ['numero' => 13, 'nombre' => 'Listado Maestro de Documentos', 'key' => 'listado_maestro'],
                ['numero' => 14, 'nombre' => 'Disposicion Final', 'key' => 'disposicion_final'],
            ]
        ]
    ];

    /**
     * @deprecated Esta constante está OBSOLETA. Los códigos ahora se obtienen de tbl_doc_plantillas.tipo_documento
     * Se mantiene temporalmente por compatibilidad con código legacy que aún no ha sido migrado.
     * NO AGREGAR NUEVOS CÓDIGOS AQUÍ. Usar tbl_doc_plantillas en su lugar.
     */
    public const CODIGOS_DOCUMENTO = [
        // OBSOLETO - Solo para compatibilidad con código legacy
        // Los nuevos documentos DEBEN usar tbl_doc_plantillas.tipo_documento
    ];

    public function __construct()
    {
        $this->db = Database::connect();
        $this->clienteModel = new ClientModel();
        $this->configService = new DocumentoConfigService();
        $this->versionService = new DocumentoVersionService();
        $this->firmanteService = new FirmanteService();
    }

    /**
     * Obtiene el código de plantilla desde la base de datos
     * Los códigos ya NO están hardcodeados - se obtienen de tbl_doc_plantillas.tipo_documento
     *
     * @param string $tipoDocumento Tipo de documento (ej: 'programa_capacitacion')
     * @return string|null Código de plantilla (ej: 'PRG-CAP') o null si no existe
     */
    protected function obtenerCodigoPlantilla(string $tipoDocumento): ?string
    {
        $plantilla = $this->db->table('tbl_doc_plantillas')
            ->select('codigo_sugerido')
            ->where('tipo_documento', $tipoDocumento)
            ->where('activo', 1)
            ->get()
            ->getRow();

        return $plantilla?->codigo_sugerido;
    }

    /**
     * Genera codigo unico para documento
     * Formato: CODIGO_PLANTILLA-XXX (ej: PRG-CAP-001)
     *
     * Los códigos se obtienen de tbl_doc_plantillas.codigo_sugerido
     * donde tipo_documento = $tipoDocumento
     */
    protected function generarCodigoDocumento(int $idCliente, string $tipoDocumento): string
    {
        // Obtener código base desde BD (NO hardcodeado)
        $codigoBase = $this->obtenerCodigoPlantilla($tipoDocumento);

        if (!$codigoBase) {
            // Si no existe en BD, registrar error y usar fallback genérico
            log_message('error', "Tipo de documento '$tipoDocumento' no tiene plantilla configurada en tbl_doc_plantillas");
            $codigoBase = 'DOC-GEN';
        }

        // Obtener consecutivo para este cliente y tipo
        $consecutivo = $this->db->table('tbl_documentos_sst')
            ->where('id_cliente', $idCliente)
            ->where('tipo_documento', $tipoDocumento)
            ->countAllResults() + 1;

        return $codigoBase . '-' . str_pad($consecutivo, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Vista para generar documento por secciones con IA
     */
    public function generarConIA(string $tipo, int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        // Obtener configuración del tipo de documento desde BD (arquitectura escalable)
        $tipoDoc = $this->configService->obtenerTipoDocumento($tipo);
        if (!$tipoDoc) {
            return redirect()->back()->with('error', 'Tipo de documento no válido');
        }

        $anio = (int)date('Y');

        // Obtener contexto del cliente
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);
        $estandares = $contexto['estandares_aplicables'] ?? 7;

        // Verificar si ya existe el documento
        $documentoExistente = $this->db->table('tbl_documentos_sst')
            ->where('id_cliente', $idCliente)
            ->where('tipo_documento', $tipo)
            ->where('anio', $anio)
            ->get()
            ->getRowArray();

        // Obtener datos para las secciones
        $cronogramaService = new CronogramaIAService();
        $ptaService = new PTAGeneratorService();
        $indicadorModel = new IndicadorSSTModel();
        $responsableModel = new ResponsableSSTModel();

        $resumenCronograma = $cronogramaService->getResumenCronograma($idCliente, $anio);
        $resumenPTA = $ptaService->getResumenPTA($idCliente, $anio);
        $indicadores = $indicadorModel->getByCliente($idCliente, true, 'capacitacion');
        $responsables = $responsableModel->getByCliente($idCliente);

        // Preparar secciones con contenido existente si hay documento
        $secciones = $tipoDoc['secciones'];
        $contenidoExistente = [];

        if ($documentoExistente) {
            $contenidoExistente = json_decode($documentoExistente['contenido'], true);

            // Normalizar secciones existentes para eliminar duplicados
            if (!empty($contenidoExistente['secciones'])) {
                $contenidoExistente['secciones'] = $this->normalizarSecciones($contenidoExistente['secciones'], $tipo);

                // Mapear contenido existente a las secciones (buscar por key o por titulo)
                foreach ($secciones as &$seccion) {
                    foreach ($contenidoExistente['secciones'] as $secExistente) {
                        $keyMatch = isset($secExistente['key']) && $secExistente['key'] === $seccion['key'];
                        $tituloMatch = isset($secExistente['titulo']) && stripos($secExistente['titulo'], $seccion['nombre']) !== false;

                        if ($keyMatch || $tituloMatch) {
                            $seccion['contenido'] = $secExistente['contenido'] ?? '';
                            $seccion['aprobado'] = $secExistente['aprobado'] ?? false;
                            break;
                        }
                    }
                }
                unset($seccion); // Romper referencia para evitar duplicación en loops posteriores
            }
        }

        // Calcular si todas las secciones estan listas (guardadas y aprobadas)
        $totalSecciones = count($secciones);
        $seccionesGuardadas = 0;
        $seccionesAprobadas = 0;

        foreach ($secciones as $seccion) {
            $contenido = $seccion['contenido'] ?? '';
            if (is_array($contenido)) {
                $contenido = $contenido['contenido'] ?? '';
            }

            if (!empty($contenido)) {
                $seccionesGuardadas++;
            }
            if (!empty($seccion['aprobado'])) {
                $seccionesAprobadas++;
            }
        }

        // El boton Vista Previa solo se habilita si:
        // 1. El documento existe en la base de datos
        // 2. TODAS las secciones estan guardadas
        // 3. TODAS las secciones estan aprobadas
        $documentoExisteEnBD = !empty($documentoExistente);
        $todasSeccionesListas = $documentoExisteEnBD &&
                                ($seccionesGuardadas === $totalSecciones) &&
                                ($seccionesAprobadas === $totalSecciones) &&
                                ($totalSecciones > 0);

        // Obtener el handler del documento desde el Factory (arquitectura escalable)
        $documentoHandler = null;
        try {
            $documentoHandler = DocumentoSSTFactory::crear($tipo);
        } catch (\Exception $e) {
            log_message('warning', "Factory no encontró handler para '{$tipo}': " . $e->getMessage());
        }

        $data = [
            'titulo' => 'Generar ' . $tipoDoc['nombre'] . ' con IA',
            'cliente' => $cliente,
            'tipo' => $tipo,
            'tipoDoc' => $tipoDoc,
            'secciones' => $secciones,
            'anio' => $anio,
            'estandares' => $estandares,
            'contexto' => $contexto,
            'documento' => $documentoExistente,
            'resumenCronograma' => $resumenCronograma,
            'resumenPTA' => $resumenPTA,
            'indicadores' => $indicadores,
            'responsables' => $responsables,
            'totalSecciones' => $totalSecciones,
            'seccionesGuardadas' => $seccionesGuardadas,
            'seccionesAprobadas' => $seccionesAprobadas,
            'todasSeccionesListas' => $todasSeccionesListas,
            // NUEVO: Handler del documento para URLs y configuración
            'documentoHandler' => $documentoHandler,
            // URLs pre-calculadas (usa Factory si disponible, fallback a convención)
            'urlVistaPrevia' => $documentoHandler
                ? $documentoHandler->getUrlVistaPrevia($idCliente, $anio)
                : base_url('documentos-sst/' . $idCliente . '/' . str_replace('_', '-', $tipo) . '/' . $anio),
        ];

        return view('documentos_sst/generar_con_ia', $data);
    }

    /**
     * Genera una seccion con IA (AJAX)
     */
    public function generarSeccionIA()
    {
        $idCliente = $this->request->getPost('id_cliente');
        $tipo = $this->request->getPost('tipo');
        $seccionKey = $this->request->getPost('seccion');
        $anio = $this->request->getPost('anio') ?? (int)date('Y');
        $contextoAdicional = $this->request->getPost('contexto_adicional') ?? '';

        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cliente no encontrado']);
        }

        // Obtener contexto
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);
        $estandares = $contexto['estandares_aplicables'] ?? 7;

        // SIEMPRE usar el servicio de IA real (OpenAI) cuando se presiona "Generar con IA"
        // El contexto base del documento (actividades, indicadores) ya está incluido en la clase del documento
        $contenido = $this->generarConIAReal($seccionKey, $cliente, $contexto, $estandares, $anio, $contextoAdicional, $tipo);

        // Obtener metadata de las consultas a BD para mostrar al usuario
        $metadataBD = null;
        try {
            $documentoHandler = DocumentoSSTFactory::crear($tipo);
            if (method_exists($documentoHandler, 'getMetadataConsultas')) {
                $metadataBD = $documentoHandler->getMetadataConsultas($cliente, $contexto);
            }
        } catch (\Exception $e) {
            // Si falla, no es crítico - solo no mostramos la metadata
            log_message('debug', "No se pudo obtener metadata de consultas: " . $e->getMessage());
        }

        return $this->response->setJSON([
            'success' => true,
            'contenido' => $contenido,
            'metadata_bd' => $metadataBD
        ]);
    }

    /**
     * Genera contenido usando el servicio de IA real (OpenAI)
     *
     * @param string $seccion Clave de la sección a generar
     * @param array $cliente Datos del cliente
     * @param array|null $contexto Contexto SST del cliente
     * @param int $estandares Nivel de estándares (7, 21, 60)
     * @param int $anio Año del documento
     * @param string $contextoAdicional Instrucciones adicionales del usuario
     * @param string $tipoDocumento Tipo de documento (usa Factory para obtener la clase correcta)
     */
    protected function generarConIAReal(string $seccion, array $cliente, ?array $contexto, int $estandares, int $anio, string $contextoAdicional, string $tipoDocumento = 'programa_capacitacion'): string
    {
        try {
            // NUEVO: Usar el Factory para obtener la clase del tipo de documento
            $documentoHandler = DocumentoSSTFactory::crear($tipoDocumento);

            // Obtener datos de la sección desde la clase específica del documento
            $nombreSeccion = $documentoHandler->getNombreSeccion($seccion);
            $numeroSeccion = $documentoHandler->getNumeroSeccion($seccion);

            // Obtener el prompt específico para este tipo de documento y sección
            $promptBase = $documentoHandler->getPromptParaSeccion($seccion, $estandares);

            // Obtener contexto base del documento
            $contextoBase = $documentoHandler->getContextoBase($cliente, $contexto);

            // Preparar datos para el servicio de IA
            $datosIA = [
                'seccion' => [
                    'numero_seccion' => $numeroSeccion,
                    'nombre_seccion' => $nombreSeccion
                ],
                'documento' => [
                    'tipo_nombre' => $documentoHandler->getNombre(),
                    'nombre' => $documentoHandler->getNombre(),
                    'tipo' => $tipoDocumento
                ],
                'cliente' => $cliente,
                'contexto' => $contexto,
                'prompt_base' => $promptBase,
                'contexto_adicional' => $contextoAdicional,
                'contexto_base' => $contextoBase
            ];

            // Llamar al servicio de IA
            $iaService = new IADocumentacionService();
            $resultado = $iaService->generarSeccion($datosIA);

            if ($resultado['success']) {
                return $resultado['contenido'];
            }

            // Si falla la IA, caer en la plantilla estática del documento específico
            log_message('warning', "IA falló para sección '{$seccion}' de '{$tipoDocumento}': " . ($resultado['error'] ?? 'Error desconocido'));
            return $documentoHandler->getContenidoEstatico($seccion, $cliente, $contexto, $estandares, $anio);

        } catch (\InvalidArgumentException $e) {
            // Si el tipo de documento no existe en el Factory, usar método legacy
            log_message('warning', "Tipo de documento '{$tipoDocumento}' no encontrado en Factory, usando método legacy: " . $e->getMessage());
            return $this->generarContenidoSeccionLegacy($seccion, $cliente, $contexto, $estandares, $anio, $tipoDocumento);
        }
    }

    /**
     * Obtiene el prompt base para una seccion especifica
     * @param string $seccion Clave de la seccion
     * @param int $estandares Nivel de estándares (7, 21, 60)
     * @param string $tipoDocumento Tipo de documento (programa_capacitacion, procedimiento_control_documental, etc.)
     */
    protected function getPromptBaseParaSeccion(string $seccion, int $estandares, string $tipoDocumento = 'programa_capacitacion'): string
    {
        // Prompts para Procedimiento de Control Documental
        if ($tipoDocumento === 'procedimiento_control_documental') {
            return $this->getPromptsControlDocumental($seccion, $estandares);
        }

        // Prompts para Programa de Capacitación (default)
        $prompts = [
            'introduccion' => "Genera una introducción para el Programa de Capacitación en SST. Debe incluir:
- Justificación de por qué la empresa necesita este programa
- Contexto de la actividad económica y sus riesgos
- Mención del marco normativo (Decreto 1072/2015, Resolución 0312/2019)
- Compromiso de la alta dirección
IMPORTANTE: Ajusta la extensión según el tamaño de empresa ({$estandares} estándares)",

            'objetivo_general' => "Genera el objetivo general del Programa de Capacitación. Debe ser un objetivo SMART (específico, medible, alcanzable, relevante, temporal) relacionado con la capacitación en SST.",

            'objetivos_especificos' => "Genera los objetivos específicos del programa.
CANTIDAD SEGÚN ESTÁNDARES:
- 7 estándares: 2-3 objetivos básicos
- 21 estándares: 3-4 objetivos
- 60 estándares: 4-5 objetivos
Deben ser SMART y relacionados con los peligros identificados de la empresa.",

            'alcance' => "Define el alcance del programa. Debe especificar:
- A quién aplica (trabajadores directos, contratistas si aplica)
- Áreas o procesos cubiertos
- Sedes incluidas
IMPORTANTE: Para empresas de 7 estándares, el alcance es simple. Máximo 5-6 ítems para 7 est, 8 ítems para 21 est, 10 ítems para 60 est.",

            'marco_legal' => "Lista el marco normativo aplicable al programa.
CANTIDAD SEGÚN ESTÁNDARES:
- 7 estándares: MÁXIMO 4-5 normas
- 21 estándares: MÁXIMO 6-8 normas
- 60 estándares: Según aplique

PROHIBIDO: NO uses tablas Markdown. Solo usa formato de lista con viñetas o negritas.",

            'definiciones' => "Genera un glosario de términos técnicos para el programa.
CANTIDAD:
- 7 estándares: MÁXIMO 8 términos esenciales
- 21 estándares: MÁXIMO 12 términos
- 60 estándares: 12-15 términos
Definiciones basadas en normativa colombiana.",

            'responsabilidades' => "Define los roles y responsabilidades para el programa.
ROLES SEGÚN ESTÁNDARES:
- 7 estándares: SOLO 3-4 roles (Representante Legal, Responsable SST, VIGÍA SST -no COPASST-, Trabajadores)
- 21 estándares: 5-6 roles (incluye COPASST)
- 60 estándares: Todos los roles necesarios
ADVERTENCIA: Si son 7 estándares, NUNCA mencionar COPASST, usar 'Vigía de SST'",

            'metodologia' => "Describe la metodología de capacitación. Incluye:
- Tipos de capacitación (teórica, práctica)
- Métodos de enseñanza
- Materiales y recursos
- Evaluación del aprendizaje",

            'cronograma' => "Genera el cronograma de capacitaciones para el año.
FRECUENCIA SEGÚN ESTÁNDARES:
- 7 estándares: Actividades TRIMESTRALES o SEMESTRALES
- 21 estándares: Actividades BIMESTRALES o TRIMESTRALES
- 60 estándares: Actividades MENSUALES
Usa formato de tabla Markdown con columnas: Mes | Tema | Duración | Dirigido a",

            'plan_trabajo' => "Resume las actividades del Plan de Trabajo Anual relacionadas con capacitación. Incluye distribución por ciclo PHVA y estado de avance.",

            'indicadores' => "Define los indicadores de gestión para el programa.
CANTIDAD:
- 7 estándares: 2-3 indicadores simples
- 21 estándares: 4-5 indicadores
- 60 estándares: 6-8 indicadores completos
Incluye fórmula, meta y periodicidad para cada uno.",

            'recursos' => "Identifica los recursos necesarios para el programa.
PROPORCIONALIDAD:
- 7 estándares: Recursos MÍNIMOS (tiempo del responsable, materiales básicos)
- 21 estándares: Recursos moderados
- 60 estándares: Recursos completos
Categorías: Humanos, Físicos, Financieros",

            'evaluacion' => "Define el mecanismo de seguimiento y evaluación del programa.
FRECUENCIA SEGÚN ESTÁNDARES:
- 7 estándares: Seguimiento TRIMESTRAL o SEMESTRAL
- 21 estándares: Seguimiento BIMESTRAL o TRIMESTRAL
- 60 estándares: Según complejidad
Incluye criterios de evaluación y responsables."
        ];

        return $prompts[$seccion] ?? "Genera el contenido para la sección '{$seccion}' del Programa de Capacitación en SST.";
    }

    /**
     * Prompts específicos para el Procedimiento de Control Documental
     */
    protected function getPromptsControlDocumental(string $seccion, int $estandares): string
    {
        $prompts = [
            'objetivo' => "Genera el objetivo del Procedimiento de Control Documental del SG-SST. Debe establecer:
- El propósito de controlar la documentación del Sistema de Gestión de SST
- La importancia de la trazabilidad y conservación documental
- Referencia al cumplimiento del estándar 2.5.1 de la Resolución 0312/2019
Máximo 2 párrafos concisos.",

            'alcance' => "Define el alcance del procedimiento. Debe especificar:
- Que aplica a TODOS los documentos del SG-SST (políticas, programas, procedimientos, formatos, matrices, etc.)
- A quién aplica (alta dirección, responsable SST, trabajadores)
- Exclusiones si las hay
IMPORTANTE: Ajustar extensión según nivel de estándares ({$estandares}).",

            'definiciones' => "Genera las definiciones clave para el control documental. INCLUIR OBLIGATORIAMENTE:
- Documento
- Registro
- Versión
- Control documental
- Listado maestro
- Documento obsoleto
- Retención documental
- Documento controlado vs No controlado
CANTIDAD: Máximo 10-12 definiciones, basadas en normativa colombiana y GTC-ISO 9001.",

            'marco_normativo' => "Lista el marco normativo aplicable al control documental del SG-SST:
- Decreto 1072 de 2015 (Art. 2.2.4.6.12 - Documentación)
- Resolución 0312 de 2019 (Estándar 2.5.1)
- Ley General de Archivos (Ley 594 de 2000)
- GTC-ISO 9001 (como referencia de buenas prácticas)
MÁXIMO 5-6 normas con breve descripción de su aplicación.",

            'responsabilidades' => "Define las responsabilidades en el control documental:
**Representante Legal:**
- Aprobar documentos de alto nivel (políticas)
- Asignar recursos para la gestión documental

**Responsable del SG-SST:**
- Elaborar y actualizar documentos
- Mantener el listado maestro
- Controlar versiones
- Gestionar la distribución

**Trabajadores:**
- Usar documentos vigentes
- Reportar necesidades de actualización

ADVERTENCIA: Si son {$estandares} estándares, ajustar roles (Vigía vs COPASST)",

            'tipos_documentos' => "Genera un párrafo introductorio sobre los tipos de documentos del SG-SST.

Explica brevemente que los documentos del Sistema de Gestión se clasifican según su naturaleza y propósito, incluyendo:
- Políticas (directrices de alto nivel aprobadas por la alta dirección)
- Programas (conjunto de actividades planificadas con objetivos específicos)
- Procedimientos (describen cómo realizar una actividad específica)
- Planes (acciones programadas para alcanzar objetivos)
- Formatos (plantillas para registro de datos e información)
- Matrices (herramientas de análisis e identificación)
- Manuales (guías completas sobre un tema)
- Reglamentos (normas internas de obligatorio cumplimiento)

IMPORTANTE: NO generes una tabla con prefijos o códigos específicos. Solo genera el texto introductorio. La tabla con los códigos reales del sistema se agregará automáticamente.",

            'codificacion' => "Genera un párrafo explicando el sistema de codificación de documentos del SG-SST.

Explica la estructura general del código:
**Estructura del código:** PREFIJO-CONSECUTIVO

Donde:
- **PREFIJO:** Identifica el tipo de documento según la clasificación del sistema
- **CONSECUTIVO:** Número secuencial de 3 dígitos (001, 002, etc.)

**Versionamiento:**
- Versión Mayor (1.0, 2.0, 3.0): Cambios significativos en estructura o contenido
- Versión Menor (1.1, 1.2, 2.1): Ajustes menores, correcciones o actualizaciones

IMPORTANTE: NO generes ejemplos de códigos específicos. Solo genera el texto explicativo. La tabla con los códigos reales configurados en el sistema se agregará automáticamente.",

            'elaboracion' => "Describe el proceso para elaborar documentos del SG-SST:

1. **Identificación de necesidad:** El responsable SST o área identifica la necesidad del documento
2. **Elaboración del borrador:** Se redacta siguiendo la estructura estándar
3. **Revisión técnica:** El responsable SST verifica el contenido
4. **Aprobación:** El Representante Legal o persona delegada aprueba
5. **Codificación:** Se asigna código según el sistema establecido
6. **Registro:** Se incluye en el Listado Maestro de Documentos

**Estructura estándar de documentos:**
- Encabezado (logo, título, código, versión, fecha)
- Objetivo
- Alcance
- Definiciones (si aplica)
- Contenido
- Responsabilidades
- Registros asociados
- Control de cambios
- Firmas de aprobación",

            'revision_aprobacion' => "Describe el flujo de revisión y aprobación de documentos:

**Niveles de aprobación:**
| Tipo de documento | Elabora | Revisa | Aprueba |
|-------------------|---------|--------|---------|
| Políticas | Responsable SST | Gerencia | Rep. Legal |
| Programas | Responsable SST | Responsable SST | Rep. Legal |
| Procedimientos | Responsable SST | Área involucrada | Responsable SST |
| Formatos | Responsable SST | - | Responsable SST |

**Firma electrónica:**
- Los documentos pueden ser firmados electrónicamente
- Cada firma incluye: Nombre, Cargo, Fecha, Firma digital
- Se genera código de verificación único

**Frecuencia de revisión:**
- Documentos estratégicos: Anual
- Documentos operativos: Según necesidad o cambios normativos",

            'distribucion' => "Describe cómo se distribuyen y controlan los documentos:

**Distribución:**
- Los documentos aprobados se publican en el sistema de gestión documental
- Se notifica a los responsables cuando hay nuevas versiones
- El acceso es según perfil de usuario (Consultor, Cliente, Trabajador)

**Control de copias:**
- Solo se consideran válidas las versiones digitales del sistema
- Las copias impresas NO son controladas
- Cada documento muestra: 'Copia controlada - Válida solo en formato digital'

**Documentos obsoletos:**
- Se marcan como 'OBSOLETO' y se retiran de circulación
- Se conservan en archivo histórico según tiempos de retención",

            'control_cambios' => "Describe el procedimiento para controlar cambios en documentos:

**Tipos de cambio:**
- **Mayor (nueva versión X.0):** Cambios en estructura, alcance o contenido significativo
- **Menor (versión X.Y):** Correcciones, actualizaciones de datos, ajustes de formato

**Proceso de cambio:**
1. Identificar necesidad de cambio
2. Elaborar propuesta de modificación
3. Revisar y aprobar cambio
4. Actualizar versión
5. Registrar en historial de cambios
6. Comunicar a usuarios

**Registro de cambios:**
Cada documento incluye tabla de control:
| Versión | Fecha | Descripción del cambio | Aprobó |
|---------|-------|------------------------|--------|

**IMPORTANTE:** Los documentos del SG-SST deben conservarse por mínimo 20 años.",

            'conservacion' => "Establece los tiempos y condiciones de conservación documental:

**Tiempos de retención según Resolución 0312/2019 y normativa laboral:**

| Tipo de documento | Tiempo mínimo | Observación |
|-------------------|---------------|-------------|
| Historias clínicas ocupacionales | 20 años | Después de retiro del trabajador |
| Exámenes médicos | 20 años | Ídem |
| Accidentes de trabajo | 20 años | Desde fecha del evento |
| Programas y procedimientos SST | 20 años | Desde última versión |
| Actas COPASST/CCL | 20 años | Desde fecha del acta |
| Capacitaciones | 20 años | Registros de asistencia |
| Matrices de peligros | 20 años | Cada versión |

**Condiciones de conservación:**
- Formato digital con respaldos periódicos
- Protección contra acceso no autorizado
- Integridad verificable (hash de documento)

**Archivo histórico:**
- Documentos obsoletos pero dentro del periodo de retención",

            'listado_maestro' => "Esta sección contendrá el LISTADO MAESTRO DE DOCUMENTOS actualizado automáticamente.

**Información incluida por cada documento:**
- Código del documento
- Nombre/Título
- Tipo de documento
- Versión vigente
- Fecha de aprobación
- Estado (Vigente/Obsoleto)
- Responsable
- Ubicación

**NOTA:** Esta sección se genera automáticamente desde el sistema, mostrando todos los documentos del SG-SST registrados para esta empresa.

El listado se actualiza cada vez que se genera o modifica un documento.",

            'disposicion_final' => "Establece qué hacer con los documentos al cumplir su tiempo de retención:

**Criterios de disposición:**
1. Verificar que se ha cumplido el tiempo de retención (20 años mínimo)
2. Confirmar que no hay procesos legales en curso que requieran el documento
3. Documentar la decisión de disposición

**Métodos de disposición:**
- **Eliminación segura:** Destrucción que impida recuperación de información
- **Transferencia:** A archivo histórico permanente (si tiene valor histórico)
- **Digitalización:** Convertir a formato digital si es papel (conservar digital)

**Acta de eliminación:**
Se debe generar acta que registre:
- Documentos eliminados (código, nombre, fechas)
- Fecha de eliminación
- Método utilizado
- Responsable de la eliminación
- Firma de autorización

**ADVERTENCIA:** Nunca eliminar documentos antes del tiempo de retención legal."
        ];

        return $prompts[$seccion] ?? "Genera el contenido para la sección '{$seccion}' del Procedimiento de Control Documental del SG-SST según la Resolución 0312/2019.";
    }

    /**
     * Genera contenido para una seccion especifica
     *
     * ARQUITECTURA: Usa el patrón Strategy a través del Factory para obtener
     * el contenido específico de cada tipo de documento.
     *
     * @param string $seccion Clave de la seccion
     * @param array $cliente Datos del cliente
     * @param array|null $contexto Contexto SST del cliente
     * @param int $estandares Nivel de estandares aplicables
     * @param int $anio Año del documento
     * @param string $contextoAdicional Instrucciones adicionales del usuario para la IA
     * @param string $tipoDocumento Tipo de documento (usa Factory para obtener la clase correcta)
     */
    protected function generarContenidoSeccion(string $seccion, array $cliente, ?array $contexto, int $estandares, int $anio, string $contextoAdicional = '', string $tipoDocumento = 'programa_capacitacion'): string
    {
        // NUEVO: Intentar usar el Factory primero (arquitectura escalable)
        try {
            if (DocumentoSSTFactory::existe($tipoDocumento)) {
                $documentoHandler = DocumentoSSTFactory::crear($tipoDocumento);
                return $documentoHandler->getContenidoEstatico($seccion, $cliente, $contexto, $estandares, $anio);
            }
        } catch (\Exception $e) {
            log_message('debug', "Factory no disponible para '{$tipoDocumento}', usando método legacy: " . $e->getMessage());
        }

        // LEGACY: Fallback al switch para programa_capacitacion (compatibilidad hacia atrás)
        return $this->generarContenidoSeccionLegacy($seccion, $cliente, $contexto, $estandares, $anio, $tipoDocumento);
    }

    /**
     * Método legacy para generar contenido de secciones (mantiene compatibilidad)
     * @deprecated Usar DocumentoSSTFactory en su lugar
     */
    protected function generarContenidoSeccionLegacy(string $seccion, array $cliente, ?array $contexto, int $estandares, int $anio, string $tipoDocumento = 'programa_capacitacion'): string
    {
        // Si hay contexto adicional del usuario, se puede usar para personalizar el contenido
        // Por ahora lo incluimos como nota al generar (en futuro puede enviarse a un servicio de IA real)
        $nombreEmpresa = $cliente['nombre_cliente'];
        $nivel = $estandares <= 7 ? 'basico (hasta 10 trabajadores, riesgo I, II o III)' :
                ($estandares <= 21 ? 'intermedio (11 a 50 trabajadores, riesgo I, II o III)' :
                'avanzado (mas de 50 trabajadores o riesgo IV y V)');

        switch ($seccion) {
            case 'introduccion':
                return "{$nombreEmpresa} en cumplimiento de la normatividad legal vigente en materia de Seguridad y Salud en el Trabajo, especificamente la Resolucion 0312 de 2019 que establece los Estandares Minimos del Sistema de Gestion de Seguridad y Salud en el Trabajo (SG-SST), ha desarrollado el presente Programa de Capacitacion.\n\n" .
                       "La empresa aplica los estandares de nivel {$nivel}, lo cual determina los requisitos minimos de capacitacion que deben cumplirse.\n\n" .
                       "La capacitacion es un elemento fundamental del SG-SST que permite a los trabajadores conocer los peligros y riesgos asociados a su labor, asi como las medidas de prevencion y control para evitar accidentes de trabajo y enfermedades laborales.";

            case 'objetivo_general':
                return "Desarrollar competencias en Seguridad y Salud en el Trabajo en todos los niveles de {$nombreEmpresa}, mediante la ejecucion de actividades de formacion y capacitacion que permitan la prevencion de accidentes de trabajo y enfermedades laborales, cumpliendo con los requisitos legales establecidos en la Resolucion 0312 de 2019.";

            case 'objetivos_especificos':
                $objetivos = [
                    "Realizar induccion y reinduccion en SST a todos los trabajadores",
                    "Capacitar a los trabajadores sobre los peligros y riesgos asociados a sus actividades",
                    "Formar a los integrantes del COPASST/Vigia SST en sus funciones y responsabilidades",
                    "Entrenar a los brigadistas de emergencias en prevencion y atencion de situaciones de emergencia"
                ];
                if ($estandares > 21) {
                    $objetivos[] = "Desarrollar competencias en los trabajadores para la identificacion de peligros y valoracion de riesgos";
                    $objetivos[] = "Promover estilos de vida y trabajo saludables";
                }
                return "- " . implode("\n- ", $objetivos);

            case 'alcance':
                return "Este programa aplica a todos los trabajadores de {$nombreEmpresa}, incluyendo trabajadores directos, contratistas, subcontratistas y visitantes que realicen actividades en las instalaciones de la empresa.";

            case 'marco_legal':
                return "El presente programa se fundamenta en la siguiente normatividad:\n\n" .
                       "- Ley 9 de 1979: Codigo Sanitario Nacional\n" .
                       "- Resolucion 2400 de 1979: Disposiciones sobre vivienda, higiene y seguridad en los establecimientos de trabajo\n" .
                       "- Decreto 1295 de 1994: Organizacion y administracion del Sistema General de Riesgos Profesionales\n" .
                       "- Ley 1562 de 2012: Sistema de Gestion de Seguridad y Salud en el Trabajo\n" .
                       "- Decreto 1072 de 2015: Decreto Unico Reglamentario del Sector Trabajo (Capitulo 6)\n" .
                       "- Resolucion 0312 de 2019: Estandares Minimos del SG-SST";

            case 'definiciones':
                return "**Capacitacion:** Proceso mediante el cual se desarrollan competencias, habilidades y destrezas en los trabajadores.\n\n" .
                       "**Induccion:** Capacitacion inicial que recibe el trabajador al ingresar a la empresa sobre aspectos generales y especificos de SST.\n\n" .
                       "**Reinduccion:** Capacitacion periodica para actualizar conocimientos y reforzar conceptos de SST.\n\n" .
                       "**Entrenamiento:** Proceso de aprendizaje practico que permite desarrollar habilidades especificas.\n\n" .
                       "**Competencia:** Capacidad demostrada para aplicar conocimientos y habilidades.";

            case 'responsabilidades':
                // Obtener responsables reales de la base de datos
                $responsableModel = new ResponsableSSTModel();
                $responsables = $responsableModel->getByCliente($cliente['id_cliente']);

                $organo = $estandares <= 10 ? 'Vigia de SST' : 'COPASST';

                // Si hay responsables registrados, usar sus datos
                if (!empty($responsables)) {
                    $contenidoResp = $responsableModel->generarContenidoParaDocumento($cliente['id_cliente'], $estandares);

                    // Agregar las funciones de cada rol
                    $contenidoResp .= "\n**Funciones en el Programa de Capacitacion:**\n\n";
                    $contenidoResp .= "**Alta Direccion:**\n" .
                           "- Asignar los recursos necesarios para la ejecucion del programa\n" .
                           "- Garantizar la participacion de los trabajadores en las capacitaciones\n\n" .
                           "**Responsable del SG-SST:**\n" .
                           "- Planificar y coordinar las actividades de capacitacion\n" .
                           "- Realizar seguimiento al cumplimiento del cronograma\n" .
                           "- Evaluar la efectividad de las capacitaciones\n" .
                           "- Mantener los registros de asistencia y evaluacion\n\n" .
                           "**{$organo}:**\n" .
                           "- Participar en las actividades de capacitacion\n" .
                           "- Proponer temas de capacitacion segun las necesidades identificadas\n" .
                           "- Verificar el cumplimiento del programa\n\n" .
                           "**Trabajadores:**\n" .
                           "- Asistir a las capacitaciones programadas\n" .
                           "- Aplicar los conocimientos adquiridos en su labor diaria\n" .
                           "- Participar activamente en las actividades de formacion";

                    return $contenidoResp;
                }

                // Si no hay responsables, mostrar plantilla genérica con aviso
                return "[PENDIENTE: Registrar responsables del SG-SST en el modulo de Responsables]\n\n" .
                       "**Alta Direccion:**\n" .
                       "- Asignar los recursos necesarios para la ejecucion del programa\n" .
                       "- Garantizar la participacion de los trabajadores en las capacitaciones\n\n" .
                       "**Responsable del SG-SST:**\n" .
                       "- Planificar y coordinar las actividades de capacitacion\n" .
                       "- Realizar seguimiento al cumplimiento del cronograma\n" .
                       "- Evaluar la efectividad de las capacitaciones\n" .
                       "- Mantener los registros de asistencia y evaluacion\n\n" .
                       "**{$organo}:**\n" .
                       "- Participar en las actividades de capacitacion\n" .
                       "- Proponer temas de capacitacion segun las necesidades identificadas\n" .
                       "- Verificar el cumplimiento del programa\n\n" .
                       "**Trabajadores:**\n" .
                       "- Asistir a las capacitaciones programadas\n" .
                       "- Aplicar los conocimientos adquiridos en su labor diaria\n" .
                       "- Participar activamente en las actividades de formacion";

            case 'metodologia':
                return "Las capacitaciones se desarrollaran utilizando las siguientes metodologias:\n\n" .
                       "**Capacitaciones Teoricas:**\n" .
                       "- Presentaciones interactivas\n" .
                       "- Material audiovisual\n" .
                       "- Documentos de apoyo\n\n" .
                       "**Capacitaciones Practicas:**\n" .
                       "- Talleres demostrativos\n" .
                       "- Simulacros\n" .
                       "- Ejercicios practicos en campo\n\n" .
                       "**Evaluacion:**\n" .
                       "- Evaluacion escrita al finalizar cada capacitacion\n" .
                       "- Evaluacion practica cuando aplique\n" .
                       "- Retroalimentacion individual";

            case 'cronograma':
                // Obtener cronograma real
                $cronogramaModel = new CronogcapacitacionModel();
                $cronogramas = $cronogramaModel
                    ->select('tbl_cronog_capacitacion.*, capacitaciones_sst.capacitacion')
                    ->join('capacitaciones_sst', 'capacitaciones_sst.id_capacitacion = tbl_cronog_capacitacion.id_capacitacion', 'left')
                    ->where('id_cliente', $cliente['id_cliente'])
                    ->where('YEAR(fecha_programada)', $anio)
                    ->orderBy('fecha_programada', 'ASC')
                    ->findAll();

                if (empty($cronogramas)) {
                    return "[PENDIENTE: Generar cronograma de capacitaciones en el modulo Generador IA]";
                }

                $meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                $contenido = "| Mes | Tema | Duracion | Dirigido a | Responsable |\n";
                $contenido .= "|-----|------|----------|------------|-------------|\n";

                foreach ($cronogramas as $c) {
                    $mes = (int)date('n', strtotime($c['fecha_programada']));
                    $contenido .= "| {$meses[$mes]} | {$c['capacitacion']} | " . ($c['horas_de_duracion_de_la_capacitacion'] ?? 1) . "h | " . ($c['perfil_de_asistentes'] ?? 'Todos') . " | " . ($c['nombre_del_capacitador'] ?? 'Responsable SST') . " |\n";
                }

                return $contenido;

            case 'plan_trabajo':
                // Obtener datos del Plan de Trabajo Anual
                $ptaService = new PTAGeneratorService();
                $resumenPTA = $ptaService->getResumenPTA($cliente['id_cliente'], $anio);

                if (empty($resumenPTA['actividades'])) {
                    return "[PENDIENTE: Generar Plan de Trabajo Anual en el modulo Generador IA]";
                }

                $contenidoPTA = "El Plan de Trabajo Anual establece las actividades a desarrollar para el cumplimiento de los objetivos del SG-SST.\n\n";
                $contenidoPTA .= "**Resumen del Plan de Trabajo {$anio}:**\n\n";
                $contenidoPTA .= "- Total de actividades: {$resumenPTA['total']}\n";
                $contenidoPTA .= "- Actividades completadas: {$resumenPTA['cerradas']}\n";
                $contenidoPTA .= "- Actividades en proceso: {$resumenPTA['en_proceso']}\n";
                $contenidoPTA .= "- Actividades pendientes: {$resumenPTA['abiertas']}\n";
                $contenidoPTA .= "- Porcentaje de avance: {$resumenPTA['porcentaje_avance']}%\n\n";

                $contenidoPTA .= "**Distribucion por ciclo PHVA:**\n\n";
                $contenidoPTA .= "| Ciclo | Cantidad |\n";
                $contenidoPTA .= "|-------|----------|\n";
                foreach ($resumenPTA['por_phva'] as $ciclo => $cantidad) {
                    $contenidoPTA .= "| {$ciclo} | {$cantidad} |\n";
                }
                $contenidoPTA .= "\n";

                // Mostrar actividades principales
                $contenidoPTA .= "**Actividades programadas:**\n\n";
                $contenidoPTA .= "| Actividad | Responsable | Fecha | PHVA | Estado |\n";
                $contenidoPTA .= "|-----------|-------------|-------|------|--------|\n";

                $contador = 0;
                foreach ($resumenPTA['actividades'] as $act) {
                    if ($contador >= 15) { // Limitar a 15 actividades para no hacer muy largo
                        $contenidoPTA .= "| ... | ... | ... | ... | ... |\n";
                        break;
                    }
                    $fecha = !empty($act['fecha_propuesta']) ? date('d/m/Y', strtotime($act['fecha_propuesta'])) : 'Por definir';
                    $estado = ucfirst(strtolower($act['estado_actividad'] ?? 'Abierta'));
                    $phva = $act['phva_plandetrabajo'] ?? 'HACER';
                    $contenidoPTA .= "| " . substr($act['actividad_plandetrabajo'] ?? 'N/A', 0, 50) . " | " .
                                     ($act['responsable_actividad'] ?? 'Por asignar') . " | " .
                                     $fecha . " | " . $phva . " | " . $estado . " |\n";
                    $contador++;
                }

                return $contenidoPTA;

            case 'indicadores':
                $indicadorModel = new IndicadorSSTModel();
                $indicadores = $indicadorModel->getByCliente($cliente['id_cliente'], true, 'capacitacion');

                if (empty($indicadores)) {
                    return "[PENDIENTE: Generar indicadores en el modulo Generador IA]";
                }

                $contenido = "El cumplimiento del programa se medira a traves de los siguientes indicadores:\n\n";
                foreach ($indicadores as $ind) {
                    $contenido .= "**{$ind['nombre_indicador']}**\n";
                    if (!empty($ind['formula'])) {
                        $contenido .= "- Formula: {$ind['formula']}\n";
                    }
                    if (!empty($ind['meta'])) {
                        $contenido .= "- Meta: {$ind['meta']}{$ind['unidad_medida']}\n";
                    }
                    $contenido .= "- Periodicidad: " . ucfirst($ind['periodicidad'] ?? 'trimestral') . "\n\n";
                }
                return $contenido;

            case 'recursos':
                // Obtener datos del Plan de Trabajo Anual filtrados por tipo de servicio "Programa de Capacitacion"
                $ptaService = new PTAGeneratorService();
                $resumenPTA = $ptaService->getResumenPTA(
                    $cliente['id_cliente'],
                    $anio,
                    PTAGeneratorService::TIPOS_SERVICIO['PROGRAMA_CAPACITACION']
                );

                $contenidoRecursos = "Para la ejecucion del programa de capacitacion se requieren los siguientes recursos:\n\n" .
                       "**Recursos Humanos:**\n" .
                       "- Responsable del SG-SST\n" .
                       "- Capacitadores internos y/o externos\n" .
                       "- ARL (Administradora de Riesgos Laborales)\n\n" .
                       "**Recursos Fisicos:**\n" .
                       "- Sala de capacitaciones o espacio adecuado\n" .
                       "- Equipos audiovisuales (computador, proyector)\n" .
                       "- Material didactico\n\n" .
                       "**Recursos Financieros:**\n" .
                       "- Presupuesto asignado por la alta direccion para actividades de capacitacion\n\n";

                // Agregar actividades del PTA que pertenecen al Programa de Capacitación
                if (!empty($resumenPTA['actividades'])) {
                    $contenidoRecursos .= "**Actividades del Plan de Trabajo Anual - Programa de Capacitacion:**\n\n";
                    $contenidoRecursos .= "| Actividad | Responsable | Estado |\n";
                    $contenidoRecursos .= "|-----------|-------------|--------|\n";

                    foreach ($resumenPTA['actividades'] as $act) {
                        $estado = ucfirst(strtolower($act['estado_actividad'] ?? 'Abierta'));
                        $contenidoRecursos .= "| " . ($act['actividad_plandetrabajo'] ?? 'N/A') . " | " .
                                             ($act['responsable_actividad'] ?? 'Por asignar') . " | " .
                                             $estado . " |\n";
                    }
                }

                return $contenidoRecursos;

            case 'evaluacion':
                return "El programa sera evaluado trimestralmente considerando:\n\n" .
                       "- Cumplimiento del cronograma de capacitaciones\n" .
                       "- Cobertura de trabajadores capacitados\n" .
                       "- Resultados de las evaluaciones aplicadas\n" .
                       "- Aplicacion de conocimientos en el trabajo\n\n" .
                       "Los resultados de la evaluacion seran presentados en las reuniones del COPASST/Vigia SST y serviran para realizar ajustes al programa segun las necesidades identificadas.";

            default:
                return "[Seccion no definida]";
        }
    }

    /**
     * Normaliza las secciones eliminando duplicados, ordenando y asegurando estructura correcta
     */
    private function normalizarSecciones(array $secciones, string $tipo): array
    {
        // Usar servicio para obtener configuración (arquitectura escalable)
        $tipoDoc = $this->configService->obtenerTipoDocumento($tipo);
        if (!$tipoDoc) {
            return $secciones;
        }

        // Indexar secciones existentes por key
        $seccionesPorKey = [];
        foreach ($secciones as $sec) {
            $secKey = $sec['key'] ?? null;
            $secTitulo = $sec['titulo'] ?? '';

            // Si no tiene key, intentar encontrar el key basado en el titulo
            if (!$secKey) {
                foreach ($tipoDoc['secciones'] as $defSec) {
                    if (stripos($secTitulo, $defSec['nombre']) !== false) {
                        $secKey = $defSec['key'];
                        break;
                    }
                }
            }

            if ($secKey) {
                // Si ya existe esta seccion, actualizar con contenido mas reciente
                if (isset($seccionesPorKey[$secKey])) {
                    if (!empty($sec['contenido'])) {
                        $seccionesPorKey[$secKey]['contenido'] = $sec['contenido'];
                    }
                    if (!empty($sec['aprobado'])) {
                        $seccionesPorKey[$secKey]['aprobado'] = $sec['aprobado'];
                    }
                } else {
                    $sec['key'] = $secKey;
                    $seccionesPorKey[$secKey] = $sec;
                }
            }
        }

        // Reconstruir array ordenado segun la estructura definida
        $seccionesOrdenadas = [];
        foreach ($tipoDoc['secciones'] as $defSec) {
            $key = $defSec['key'];
            if (isset($seccionesPorKey[$key])) {
                // Usar seccion existente pero con titulo actualizado
                $seccion = $seccionesPorKey[$key];
                $seccion['titulo'] = $defSec['numero'] . '. ' . strtoupper($defSec['nombre']);
                $seccion['key'] = $key;
                $seccionesOrdenadas[] = $seccion;
            } else {
                // Crear seccion vacia
                $seccionesOrdenadas[] = [
                    'titulo' => $defSec['numero'] . '. ' . strtoupper($defSec['nombre']),
                    'contenido' => '',
                    'key' => $key,
                    'aprobado' => false
                ];
            }
        }

        return $seccionesOrdenadas;
    }

    /**
     * Guarda una seccion editada (AJAX)
     */
    public function guardarSeccion()
    {
        $idCliente = $this->request->getPost('id_cliente');
        $tipo = $this->request->getPost('tipo');
        $seccionKey = $this->request->getPost('seccion');
        $contenido = $this->request->getPost('contenido');
        $anio = $this->request->getPost('anio') ?? (int)date('Y');

        // Obtener información de la sección desde servicio (arquitectura escalable)
        $tipoDoc = $this->configService->obtenerTipoDocumento($tipo);
        $nombreSeccion = $seccionKey;
        $numeroSeccion = 0;
        if ($tipoDoc) {
            foreach ($tipoDoc['secciones'] as $s) {
                if ($s['key'] === $seccionKey) {
                    $nombreSeccion = $s['nombre'];
                    $numeroSeccion = $s['numero'];
                    break;
                }
            }
        }

        // Obtener o crear documento
        $documento = $this->db->table('tbl_documentos_sst')
            ->where('id_cliente', $idCliente)
            ->where('tipo_documento', $tipo)
            ->where('anio', $anio)
            ->get()
            ->getRowArray();

        $contenidoDoc = $documento ? json_decode($documento['contenido'], true) : ['secciones' => []];

        // Actualizar seccion - buscar por key O por titulo (formato antiguo "N. NOMBRE")
        $encontrado = false;
        $tituloAntiguo = $numeroSeccion . '. ' . strtoupper($nombreSeccion);

        foreach ($contenidoDoc['secciones'] as $idx => &$sec) {
            $secKey = $sec['key'] ?? '';
            $secTitulo = $sec['titulo'] ?? '';

            // Buscar por key exacto o por titulo antiguo
            if ($secKey === $seccionKey ||
                stripos($secTitulo, $nombreSeccion) !== false ||
                $secTitulo === $tituloAntiguo) {

                // Actualizar la seccion manteniendo compatibilidad
                $sec['key'] = $seccionKey;
                $sec['titulo'] = $numeroSeccion . '. ' . strtoupper($nombreSeccion);
                $sec['contenido'] = $contenido;
                $encontrado = true;
                break;
            }
        }

        if (!$encontrado) {
            $contenidoDoc['secciones'][] = [
                'key' => $seccionKey,
                'titulo' => $numeroSeccion . '. ' . strtoupper($nombreSeccion),
                'contenido' => $contenido,
                'aprobado' => false
            ];
        }

        // Normalizar secciones para eliminar duplicados
        $contenidoDoc['secciones'] = $this->normalizarSecciones($contenidoDoc['secciones'], $tipo);

        // Guardar
        if ($documento) {
            $this->db->table('tbl_documentos_sst')
                ->where('id_documento', $documento['id_documento'])
                ->update([
                    'contenido' => json_encode($contenidoDoc, JSON_UNESCAPED_UNICODE),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
        } else {
            // Generar codigo unico usando SP
            $codigoDocumento = $this->generarCodigoDocumento($idCliente, $tipo);

            // Obtener nombre del documento desde servicio
            $tipoDocConfig = $this->configService->obtenerTipoDocumento($tipo);
            $this->db->table('tbl_documentos_sst')->insert([
                'id_cliente' => $idCliente,
                'tipo_documento' => $tipo,
                'codigo' => $codigoDocumento,
                'titulo' => $tipoDocConfig['nombre'] ?? 'Documento SST',
                'anio' => $anio,
                'contenido' => json_encode($contenidoDoc, JSON_UNESCAPED_UNICODE),
                'version' => 1,
                'estado' => 'borrador',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Seccion guardada']);
    }

    /**
     * Aprueba una seccion (AJAX)
     */
    public function aprobarSeccion()
    {
        $idCliente = $this->request->getPost('id_cliente');
        $tipo = $this->request->getPost('tipo');
        $seccionKey = $this->request->getPost('seccion');
        $anio = $this->request->getPost('anio') ?? (int)date('Y');

        $documento = $this->db->table('tbl_documentos_sst')
            ->where('id_cliente', $idCliente)
            ->where('tipo_documento', $tipo)
            ->where('anio', $anio)
            ->get()
            ->getRowArray();

        if (!$documento) {
            return $this->response->setJSON(['success' => false, 'message' => 'Documento no encontrado']);
        }

        $contenidoDoc = json_decode($documento['contenido'], true);

        // Obtener información de la sección desde servicio (arquitectura escalable)
        $tipoDoc = $this->configService->obtenerTipoDocumento($tipo);
        $nombreSeccion = $seccionKey;
        $numeroSeccion = 0;
        if ($tipoDoc) {
            foreach ($tipoDoc['secciones'] as $s) {
                if ($s['key'] === $seccionKey) {
                    $nombreSeccion = $s['nombre'];
                    $numeroSeccion = $s['numero'];
                    break;
                }
            }
        }

        $tituloAntiguo = $numeroSeccion . '. ' . strtoupper($nombreSeccion);
        $encontrado = false;

        foreach ($contenidoDoc['secciones'] as &$sec) {
            $secKey = $sec['key'] ?? '';
            $secTitulo = $sec['titulo'] ?? '';

            // Buscar por key exacto o por titulo antiguo
            if ($secKey === $seccionKey ||
                stripos($secTitulo, $nombreSeccion) !== false ||
                $secTitulo === $tituloAntiguo) {

                $sec['key'] = $seccionKey; // Asegurar que tenga key para futuras busquedas
                $sec['aprobado'] = true;
                $encontrado = true;
                break;
            }
        }

        if (!$encontrado) {
            return $this->response->setJSON(['success' => false, 'message' => 'Seccion no encontrada en el documento']);
        }

        $this->db->table('tbl_documentos_sst')
            ->where('id_documento', $documento['id_documento'])
            ->update([
                'contenido' => json_encode($contenidoDoc, JSON_UNESCAPED_UNICODE),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        return $this->response->setJSON(['success' => true, 'message' => 'Seccion aprobada']);
    }

    /**
     * Genera PDF del documento
     */
    public function generarPDF(int $idDocumento)
    {
        $documento = $this->db->table('tbl_documentos_sst')
            ->where('id_documento', $idDocumento)
            ->get()
            ->getRowArray();

        if (!$documento) {
            return redirect()->back()->with('error', 'Documento no encontrado');
        }

        $cliente = $this->clienteModel->find($documento['id_cliente']);
        $contenido = json_decode($documento['contenido'], true);

        // Normalizar secciones para eliminar duplicados
        if (!empty($contenido['secciones'])) {
            $contenido['secciones'] = $this->normalizarSecciones($contenido['secciones'], $documento['tipo_documento']);
        }

        $data = [
            'titulo' => $documento['titulo'],
            'cliente' => $cliente,
            'documento' => $documento,
            'contenido' => $contenido,
            'anio' => $documento['anio']
        ];

        return view('documentos_sst/programa_capacitacion', $data);
    }

    /**
     * Muestra el Programa de Capacitacion generado
     */
    public function programaCapacitacion(int $idCliente, int $anio)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $documento = $this->db->table('tbl_documentos_sst')
            ->where('id_cliente', $idCliente)
            ->where('tipo_documento', 'programa_capacitacion')
            ->where('anio', $anio)
            ->get()
            ->getRowArray();

        if (!$documento) {
            return redirect()->to(base_url('generador-ia/' . $idCliente))
                ->with('error', 'Documento no encontrado. Genere primero el Programa de Capacitacion.');
        }

        $contenido = json_decode($documento['contenido'], true);

        // Normalizar secciones para eliminar duplicados
        if (!empty($contenido['secciones'])) {
            $contenido['secciones'] = $this->normalizarSecciones($contenido['secciones'], 'programa_capacitacion');
        }

        // Obtener historial de versiones para la tabla de Control de Cambios
        $versiones = $this->db->table('tbl_doc_versiones_sst')
            ->where('id_documento', $documento['id_documento'])
            ->orderBy('fecha_autorizacion', 'ASC')
            ->get()
            ->getResultArray();

        // Obtener responsables del cliente para las firmas
        $responsableModel = new ResponsableSSTModel();
        $responsables = $responsableModel->getByCliente($idCliente);

        // Obtener contexto SST para datos adicionales
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);

        // Obtener datos del consultor asignado
        $consultor = null;
        $idConsultor = $contexto['id_consultor_responsable'] ?? $cliente['id_consultor'] ?? null;
        if ($idConsultor) {
            $consultorModel = new \App\Models\ConsultantModel();
            $consultor = $consultorModel->find($idConsultor);
        }

        // Obtener firmas electrónicas del documento
        $firmasElectronicas = [];
        $solicitudesFirma = $this->db->table('tbl_doc_firma_solicitudes')
            ->where('id_documento', $documento['id_documento'])
            ->where('estado', 'firmado')
            ->get()
            ->getResultArray();

        foreach ($solicitudesFirma as $sol) {
            $evidencia = $this->db->table('tbl_doc_firma_evidencias')
                ->where('id_solicitud', $sol['id_solicitud'])
                ->get()
                ->getRowArray();
            $firmasElectronicas[$sol['firmante_tipo']] = [
                'solicitud' => $sol,
                'evidencia' => $evidencia
            ];
        }

        $data = [
            'titulo' => 'Programa de Capacitacion - ' . $cliente['nombre_cliente'],
            'cliente' => $cliente,
            'documento' => $documento,
            'contenido' => $contenido,
            'anio' => $anio,
            'versiones' => $versiones,
            'responsables' => $responsables,
            'contexto' => $contexto,
            'consultor' => $consultor,
            'firmasElectronicas' => $firmasElectronicas
        ];

        return view('documentos_sst/programa_capacitacion', $data);
    }

    /**
     * Vista previa del Programa de Induccion y Reinduccion (1.2.2)
     */
    public function programaInduccionReinduccion(int $idCliente, int $anio)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $documento = $this->db->table('tbl_documentos_sst')
            ->where('id_cliente', $idCliente)
            ->where('tipo_documento', 'programa_induccion_reinduccion')
            ->where('anio', $anio)
            ->get()
            ->getRowArray();

        if (!$documento) {
            return redirect()->to(base_url('documentos/generar/programa_induccion_reinduccion/' . $idCliente))
                ->with('error', 'Documento no encontrado. Genere primero el Programa de Induccion y Reinduccion.');
        }

        $contenido = json_decode($documento['contenido'], true);

        // Normalizar secciones para eliminar duplicados
        if (!empty($contenido['secciones'])) {
            $contenido['secciones'] = $this->normalizarSecciones($contenido['secciones'], 'programa_induccion_reinduccion');
        }

        // Obtener historial de versiones para la tabla de Control de Cambios
        $versiones = $this->db->table('tbl_doc_versiones_sst')
            ->where('id_documento', $documento['id_documento'])
            ->orderBy('fecha_autorizacion', 'ASC')
            ->get()
            ->getResultArray();

        // Obtener responsables del cliente para las firmas
        $responsableModel = new ResponsableSSTModel();
        $responsables = $responsableModel->getByCliente($idCliente);

        // Obtener contexto SST para datos adicionales
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);

        // Obtener datos del consultor asignado
        $consultor = null;
        $idConsultor = $contexto['id_consultor_responsable'] ?? $cliente['id_consultor'] ?? null;
        if ($idConsultor) {
            $consultorModel = new \App\Models\ConsultantModel();
            $consultor = $consultorModel->find($idConsultor);
        }

        // Obtener firmas electrónicas del documento
        $firmasElectronicas = [];
        $solicitudesFirma = $this->db->table('tbl_doc_firma_solicitudes')
            ->where('id_documento', $documento['id_documento'])
            ->where('estado', 'firmado')
            ->get()
            ->getResultArray();

        foreach ($solicitudesFirma as $sol) {
            $evidencia = $this->db->table('tbl_doc_firma_evidencias')
                ->where('id_solicitud', $sol['id_solicitud'])
                ->get()
                ->getRowArray();
            $firmasElectronicas[$sol['firmante_tipo']] = [
                'solicitud' => $sol,
                'evidencia' => $evidencia
            ];
        }

        $data = [
            'titulo' => 'Programa de Induccion y Reinduccion - ' . $cliente['nombre_cliente'],
            'cliente' => $cliente,
            'documento' => $documento,
            'contenido' => $contenido,
            'anio' => $anio,
            'versiones' => $versiones,
            'responsables' => $responsables,
            'contexto' => $contexto,
            'consultor' => $consultor,
            'firmasElectronicas' => $firmasElectronicas
        ];

        return view('documentos_sst/programa_induccion_reinduccion', $data);
    }

    /**
     * Vista previa del Programa de Promocion y Prevencion en Salud (3.1.2)
     */
    public function programaPromocionPrevencionSalud(int $idCliente, int $anio)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $documento = $this->db->table('tbl_documentos_sst')
            ->where('id_cliente', $idCliente)
            ->where('tipo_documento', 'programa_promocion_prevencion_salud')
            ->where('anio', $anio)
            ->get()
            ->getRowArray();

        if (!$documento) {
            return redirect()->to(base_url('generador-ia/' . $idCliente))
                ->with('error', 'Documento no encontrado. Genere primero el Programa de Promocion y Prevencion en Salud.');
        }

        $contenido = json_decode($documento['contenido'], true);

        // Normalizar secciones para eliminar duplicados
        if (!empty($contenido['secciones'])) {
            $contenido['secciones'] = $this->normalizarSecciones($contenido['secciones'], 'programa_promocion_prevencion_salud');
        }

        // Obtener historial de versiones para la tabla de Control de Cambios
        $versiones = $this->db->table('tbl_doc_versiones_sst')
            ->where('id_documento', $documento['id_documento'])
            ->orderBy('fecha_autorizacion', 'ASC')
            ->get()
            ->getResultArray();

        // Obtener responsables del cliente para las firmas
        $responsableModel = new ResponsableSSTModel();
        $responsables = $responsableModel->getByCliente($idCliente);

        // Obtener contexto SST para datos adicionales
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);

        // Obtener datos del consultor asignado
        $consultor = null;
        $idConsultor = $contexto['id_consultor_responsable'] ?? $cliente['id_consultor'] ?? null;
        if ($idConsultor) {
            $consultorModel = new \App\Models\ConsultantModel();
            $consultor = $consultorModel->find($idConsultor);
        }

        // Obtener firmas electronicas del documento
        $firmasElectronicas = [];
        $solicitudesFirma = $this->db->table('tbl_doc_firma_solicitudes')
            ->where('id_documento', $documento['id_documento'])
            ->where('estado', 'firmado')
            ->get()
            ->getResultArray();

        foreach ($solicitudesFirma as $sol) {
            $evidencia = $this->db->table('tbl_doc_firma_evidencias')
                ->where('id_solicitud', $sol['id_solicitud'])
                ->get()
                ->getRowArray();
            $firmasElectronicas[$sol['firmante_tipo']] = [
                'solicitud' => $sol,
                'evidencia' => $evidencia
            ];
        }

        $data = [
            'titulo' => 'Programa de Promocion y Prevencion en Salud - ' . $cliente['nombre_cliente'],
            'cliente' => $cliente,
            'documento' => $documento,
            'contenido' => $contenido,
            'anio' => $anio,
            'versiones' => $versiones,
            'responsables' => $responsables,
            'contexto' => $contexto,
            'consultor' => $consultor,
            'firmasElectronicas' => $firmasElectronicas
        ];

        return view('documentos_sst/programa_promocion_prevencion_salud', $data);
    }

    /**
     * Exporta el documento a PDF usando Dompdf
     */
    public function exportarPDF(int $idDocumento)
    {
        $documento = $this->db->table('tbl_documentos_sst')
            ->where('id_documento', $idDocumento)
            ->get()
            ->getRowArray();

        if (!$documento) {
            return redirect()->back()->with('error', 'Documento no encontrado');
        }

        $cliente = $this->clienteModel->find($documento['id_cliente']);
        $contenido = json_decode($documento['contenido'], true);

        // Normalizar secciones
        if (!empty($contenido['secciones'])) {
            $contenido['secciones'] = $this->normalizarSecciones($contenido['secciones'], $documento['tipo_documento']);
        }

        // Preparar logo como base64 para el PDF
        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoData = file_get_contents($logoPath);
                $logoMime = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode($logoData);
            }
        }

        // Obtener historial de versiones
        $versiones = $this->db->table('tbl_doc_versiones_sst')
            ->where('id_documento', $idDocumento)
            ->orderBy('fecha_autorizacion', 'ASC')
            ->get()
            ->getResultArray();

        // Obtener responsables del cliente
        $responsableModel = new ResponsableSSTModel();
        $responsables = $responsableModel->getByCliente($documento['id_cliente']);

        // Obtener contexto SST
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($documento['id_cliente']);

        // Obtener datos del consultor asignado
        $consultor = null;
        $firmaConsultorBase64 = '';
        $idConsultor = $contexto['id_consultor_responsable'] ?? $cliente['id_consultor'] ?? null;
        if ($idConsultor) {
            $consultorModel = new \App\Models\ConsultantModel();
            $consultor = $consultorModel->find($idConsultor);

            // Preparar firma del consultor como base64 para PDF
            if (!empty($consultor['firma_consultor'])) {
                $firmaPath = FCPATH . 'uploads/' . $consultor['firma_consultor'];
                if (file_exists($firmaPath)) {
                    $firmaData = file_get_contents($firmaPath);
                    $firmaMime = mime_content_type($firmaPath);
                    $firmaConsultorBase64 = 'data:' . $firmaMime . ';base64,' . base64_encode($firmaData);
                }
            }
        }

        // Obtener firmas electrónicas del documento para el PDF
        $firmasElectronicas = [];
        $solicitudesFirma = $this->db->table('tbl_doc_firma_solicitudes')
            ->where('id_documento', $idDocumento)
            ->where('estado', 'firmado')
            ->get()
            ->getResultArray();

        foreach ($solicitudesFirma as $sol) {
            $evidencia = $this->db->table('tbl_doc_firma_evidencias')
                ->where('id_solicitud', $sol['id_solicitud'])
                ->get()
                ->getRowArray();
            $firmasElectronicas[$sol['firmante_tipo']] = [
                'solicitud' => $sol,
                'evidencia' => $evidencia
            ];
        }

        // Datos dinámicos para secciones especiales (tipos_documentos, codificacion, listado_maestro)
        $listadoMaestro = [];
        $tiposDocumento = [];
        $plantillas = [];

        // Solo cargar si es documento de control documental
        if ($documento['tipo_documento'] === 'procedimiento_control_documental') {
            // Listado Maestro de Documentos del cliente
            $listadoMaestro = $this->db->table('tbl_documentos_sst d')
                ->select('d.id_documento, d.codigo, d.titulo, d.tipo_documento, d.version, d.estado, d.created_at, d.updated_at')
                ->where('d.id_cliente', $documento['id_cliente'])
                ->whereIn('d.estado', ['aprobado', 'firmado', 'generado'])
                ->orderBy('d.codigo', 'ASC')
                ->get()
                ->getResultArray();

            // Tipos de documentos del sistema
            $tiposDocumento = $this->db->table('tbl_doc_tipos')
                ->where('activo', 1)
                ->orderBy('id_tipo')
                ->get()
                ->getResultArray();

            // Plantillas para la codificación
            $plantillas = $this->db->table('tbl_doc_plantillas')
                ->select('codigo_sugerido, nombre, tipo_documento')
                ->where('activo', 1)
                ->where('tipo_documento IS NOT NULL')
                ->orderBy('codigo_sugerido')
                ->get()
                ->getResultArray();
        }

        $data = [
            'titulo' => $documento['titulo'],
            'cliente' => $cliente,
            'documento' => $documento,
            'contenido' => $contenido,
            'anio' => $documento['anio'],
            'logoBase64' => $logoBase64,
            'versiones' => $versiones,
            'responsables' => $responsables,
            'contexto' => $contexto,
            'consultor' => $consultor,
            'firmaConsultorBase64' => $firmaConsultorBase64,
            'firmasElectronicas' => $firmasElectronicas,
            // Datos dinámicos para secciones especiales
            'listadoMaestro' => $listadoMaestro,
            'tiposDocumento' => $tiposDocumento,
            'plantillas' => $plantillas,
            // Firmantes desde servicio (arquitectura escalable)
            'firmantesDefinidos' => $this->configService->obtenerFirmantes($documento['tipo_documento'])
        ];

        // Renderizar la vista del PDF
        $html = view('documentos_sst/pdf_template', $data);

        // Crear instancia de Dompdf
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        // Nombre del archivo
        $nombreArchivo = $documento['codigo'] . '_' . url_title($documento['titulo'], '-', true) . '.pdf';

        // Descargar
        $dompdf->stream($nombreArchivo, ['Attachment' => true]);
    }

    /**
     * Publica el documento como PDF en tbl_reporte (reportList) para consulta rápida
     */
    public function publicarPDF(int $idDocumento)
    {
        $documento = $this->db->table('tbl_documentos_sst')
            ->where('id_documento', $idDocumento)
            ->get()
            ->getRowArray();

        if (!$documento) {
            return redirect()->back()->with('error', 'Documento no encontrado');
        }

        $cliente = $this->clienteModel->find($documento['id_cliente']);
        $contenido = json_decode($documento['contenido'], true);

        if (!empty($contenido['secciones'])) {
            $contenido['secciones'] = $this->normalizarSecciones($contenido['secciones'], $documento['tipo_documento']);
        }

        // Logo base64
        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoData = file_get_contents($logoPath);
                $logoMime = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode($logoData);
            }
        }

        // Versiones
        $versiones = $this->db->table('tbl_doc_versiones_sst')
            ->where('id_documento', $idDocumento)
            ->orderBy('fecha_autorizacion', 'ASC')
            ->get()
            ->getResultArray();

        // Responsables
        $responsableModel = new ResponsableSSTModel();
        $responsables = $responsableModel->getByCliente($documento['id_cliente']);

        // Contexto
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($documento['id_cliente']);

        // Consultor y firma
        $consultor = null;
        $firmaConsultorBase64 = '';
        $idConsultor = $contexto['id_consultor_responsable'] ?? $cliente['id_consultor'] ?? null;
        if ($idConsultor) {
            $consultorModel = new \App\Models\ConsultantModel();
            $consultor = $consultorModel->find($idConsultor);
            if (!empty($consultor['firma_consultor'])) {
                $firmaPath = FCPATH . 'uploads/' . $consultor['firma_consultor'];
                if (file_exists($firmaPath)) {
                    $firmaData = file_get_contents($firmaPath);
                    $firmaMime = mime_content_type($firmaPath);
                    $firmaConsultorBase64 = 'data:' . $firmaMime . ';base64,' . base64_encode($firmaData);
                }
            }
        }

        // Firmas electrónicas
        $firmasElectronicas = [];
        $solicitudesFirma = $this->db->table('tbl_doc_firma_solicitudes')
            ->where('id_documento', $idDocumento)
            ->where('estado', 'firmado')
            ->get()
            ->getResultArray();

        foreach ($solicitudesFirma as $sol) {
            $evidencia = $this->db->table('tbl_doc_firma_evidencias')
                ->where('id_solicitud', $sol['id_solicitud'])
                ->get()
                ->getRowArray();
            $firmasElectronicas[$sol['firmante_tipo']] = [
                'solicitud' => $sol,
                'evidencia' => $evidencia
            ];
        }

        // Datos dinámicos para secciones especiales (tipos_documentos, codificacion, listado_maestro)
        $listadoMaestro = [];
        $tiposDocumento = [];
        $plantillas = [];

        // Solo cargar si es documento de control documental
        if ($documento['tipo_documento'] === 'procedimiento_control_documental') {
            $listadoMaestro = $this->db->table('tbl_documentos_sst d')
                ->select('d.id_documento, d.codigo, d.titulo, d.tipo_documento, d.version, d.estado, d.created_at, d.updated_at')
                ->where('d.id_cliente', $documento['id_cliente'])
                ->whereIn('d.estado', ['aprobado', 'firmado', 'generado'])
                ->orderBy('d.codigo', 'ASC')
                ->get()
                ->getResultArray();

            $tiposDocumento = $this->db->table('tbl_doc_tipos')
                ->where('activo', 1)
                ->orderBy('id_tipo')
                ->get()
                ->getResultArray();

            $plantillas = $this->db->table('tbl_doc_plantillas')
                ->select('codigo_sugerido, nombre, tipo_documento')
                ->where('activo', 1)
                ->where('tipo_documento IS NOT NULL')
                ->orderBy('codigo_sugerido')
                ->get()
                ->getResultArray();
        }

        $data = [
            'titulo' => $documento['titulo'],
            'cliente' => $cliente,
            'documento' => $documento,
            'contenido' => $contenido,
            'anio' => $documento['anio'],
            'logoBase64' => $logoBase64,
            'versiones' => $versiones,
            'responsables' => $responsables,
            'contexto' => $contexto,
            'consultor' => $consultor,
            'firmaConsultorBase64' => $firmaConsultorBase64,
            'firmasElectronicas' => $firmasElectronicas,
            // Datos dinámicos para secciones especiales
            'listadoMaestro' => $listadoMaestro,
            'tiposDocumento' => $tiposDocumento,
            'plantillas' => $plantillas,
            // Firmantes desde servicio (arquitectura escalable)
            'firmantesDefinidos' => $this->configService->obtenerFirmantes($documento['tipo_documento'])
        ];

        // Renderizar HTML y generar PDF
        $html = view('documentos_sst/pdf_template', $data);
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();
        $pdfOutput = $dompdf->output();

        // Guardar archivo en uploads/{nit}/
        $nit = $cliente['nit_cliente'] ?? $documento['id_cliente'];
        $uploadDir = FCPATH . 'uploads/' . $nit;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = time() . '_' . url_title(($documento['codigo'] ?? 'DOC') . '_' . $documento['titulo'], '-', true) . '.pdf';
        $filePath = $uploadDir . '/' . $fileName;
        file_put_contents($filePath, $pdfOutput);

        $enlace = base_url('uploads/' . $nit . '/' . $fileName);

        // Obtener ID del detail_report "Documento SG-SST"
        $detailReport = $this->db->table('detail_report')
            ->where('detail_report', 'Documento SG-SST')
            ->get()
            ->getRowArray();
        $idDetailReport = $detailReport['id_detailreport'] ?? 2;

        // Verificar si ya existe un reporte para este documento (evitar duplicados)
        $codigoBusqueda = $documento['codigo'] ?? $documento['titulo'];
        $existente = $this->db->table('tbl_reporte')
            ->where("titulo_reporte COLLATE utf8mb4_general_ci LIKE '%" . $this->db->escapeLikeString($codigoBusqueda) . "%'", null, false)
            ->where('id_cliente', $documento['id_cliente'])
            ->where('id_detailreport', $idDetailReport)
            ->get()
            ->getRowArray();

        $idReportType = 12; // Reportes SST

        $estadoDoc = $documento['estado'] ?? 'borrador';
        $tituloReporte = ($documento['codigo'] ?? '') . ' - ' . $documento['titulo'] . ' (v' . ($documento['version'] ?? '1') . ')';

        if ($existente) {
            // Actualizar el reporte existente con el nuevo PDF
            $this->db->table('tbl_reporte')
                ->where('id_reporte', $existente['id_reporte'])
                ->update([
                    'titulo_reporte' => $tituloReporte,
                    'enlace' => $enlace,
                    'estado' => 'CERRADO',
                    'observaciones' => 'PDF actualizado manualmente. Estado: ' . $estadoDoc . '. Año: ' . $documento['anio'],
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
        } else {
            // Insertar nuevo registro
            $this->db->table('tbl_reporte')->insert([
                'titulo_reporte' => $tituloReporte,
                'id_detailreport' => $idDetailReport,
                'id_report_type' => $idReportType,
                'id_cliente' => $documento['id_cliente'],
                'enlace' => $enlace,
                'estado' => 'CERRADO',
                'observaciones' => 'Documento publicado manualmente. Estado: ' . $estadoDoc . '. Año: ' . $documento['anio'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        // Guardar enlace PDF en la versión vigente
        $this->db->table('tbl_doc_versiones_sst')
            ->where('id_documento', $idDocumento)
            ->where('estado', 'vigente')
            ->update(['archivo_pdf' => $enlace]);

        return redirect()->to('documentacion/' . $documento['id_cliente'])
            ->with('success', 'Documento publicado exitosamente en Reportes. Ya es consultable desde reportList.');
    }

    /**
     * Adjunta un documento escaneado firmado físicamente y lo publica en reportList
     * Usado para documentos como "Responsabilidades de Trabajadores" donde los trabajadores
     * firman en papel y luego se escanea el documento.
     */
    public function adjuntarFirmado()
    {
        $idDocumento = $this->request->getPost('id_documento');
        $observaciones = $this->request->getPost('observaciones') ?? '';

        $documento = $this->db->table('tbl_documentos_sst')
            ->where('id_documento', $idDocumento)
            ->get()
            ->getRowArray();

        if (!$documento) {
            return redirect()->back()->with('error', 'Documento no encontrado');
        }

        $cliente = $this->clienteModel->find($documento['id_cliente']);

        // Validar y subir archivo
        $archivo = $this->request->getFile('archivo_firmado');

        if (!$archivo || !$archivo->isValid()) {
            return redirect()->back()->with('error', 'Error al subir el archivo. Intente nuevamente.');
        }

        // Validar tipo de archivo
        $tiposPermitidos = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($archivo->getMimeType(), $tiposPermitidos)) {
            return redirect()->back()->with('error', 'Tipo de archivo no permitido. Use PDF, JPG o PNG.');
        }

        // Validar tamaño (10MB máximo)
        if ($archivo->getSize() > 10 * 1024 * 1024) {
            return redirect()->back()->with('error', 'El archivo excede el tamaño máximo de 10MB.');
        }

        // Crear directorio si no existe
        $carpetaNit = $cliente['nit_cliente'];
        $uploadPath = FCPATH . 'uploads/' . $carpetaNit;
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        // Generar nombre único para el archivo
        $extension = $archivo->getExtension();
        $nombreArchivo = 'firmado_' . $documento['tipo_documento'] . '_' . date('Ymd_His') . '.' . $extension;

        // Mover archivo
        if (!$archivo->move($uploadPath, $nombreArchivo)) {
            return redirect()->back()->with('error', 'Error al guardar el archivo en el servidor.');
        }

        // Enlace público
        $enlace = base_url('uploads/' . $carpetaNit . '/' . $nombreArchivo);

        // Buscar o crear detail_report para documentos SST
        $detailReport = $this->db->table('detail_report')
            ->where("detail_report COLLATE utf8mb4_general_ci LIKE '%Documento SG-SST%'", null, false)
            ->get()
            ->getRowArray();
        $idDetailReport = $detailReport['id_detailreport'] ?? 2;

        // Verificar si ya existe un reporte para este documento
        $codigoBusqueda = $documento['codigo'] ?? $documento['titulo'];
        $existente = $this->db->table('tbl_reporte')
            ->where("titulo_reporte COLLATE utf8mb4_general_ci LIKE '%" . $this->db->escapeLikeString($codigoBusqueda) . "%'", null, false)
            ->where('id_cliente', $documento['id_cliente'])
            ->where('id_detailreport', $idDetailReport)
            ->get()
            ->getRowArray();

        $idReportType = 12; // Reportes SST
        $tituloReporte = ($documento['codigo'] ?? '') . ' - ' . $documento['titulo'] . ' (FIRMADO v' . ($documento['version'] ?? '1') . ')';
        $obsReporte = 'Documento escaneado con firmas físicas. ' . ($observaciones ? $observaciones . '. ' : '') . 'Año: ' . $documento['anio'];

        if ($existente) {
            // Actualizar reporte existente
            $this->db->table('tbl_reporte')
                ->where('id_reporte', $existente['id_reporte'])
                ->update([
                    'titulo_reporte' => $tituloReporte,
                    'enlace' => $enlace,
                    'estado' => 'CERRADO',
                    'observaciones' => $obsReporte,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
        } else {
            // Insertar nuevo reporte
            $this->db->table('tbl_reporte')->insert([
                'titulo_reporte' => $tituloReporte,
                'id_detailreport' => $idDetailReport,
                'id_report_type' => $idReportType,
                'id_cliente' => $documento['id_cliente'],
                'enlace' => $enlace,
                'estado' => 'CERRADO',
                'observaciones' => $obsReporte,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        // Actualizar estado del documento a firmado
        $this->db->table('tbl_documentos_sst')
            ->where('id_documento', $idDocumento)
            ->update([
                'estado' => 'firmado',
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        // Guardar enlace del archivo en la versión vigente
        $this->db->table('tbl_doc_versiones_sst')
            ->where('id_documento', $idDocumento)
            ->where('estado', 'vigente')
            ->update([
                'archivo_pdf' => $enlace,
                'estado' => 'vigente'
            ]);

        return redirect()->to('documentacion/carpeta/' . $documento['id_cliente'])
            ->with('success', 'Documento firmado adjuntado y publicado en Reportes exitosamente.');
    }

    /**
     * Adjunta una planilla de afiliación al Sistema General de Riesgos Laborales (1.1.4)
     * Soporta archivos (PDF, Excel, imágenes) o enlaces externos (Google Drive, OneDrive)
     * Publica automáticamente en reportList para consulta
     */
    public function adjuntarPlanillaSRL()
    {
        $idCliente = $this->request->getPost('id_cliente');
        $idCarpeta = $this->request->getPost('id_carpeta');
        $tipoCarga = $this->request->getPost('tipo_carga'); // 'archivo' o 'enlace'
        $descripcion = $this->request->getPost('descripcion');
        $observaciones = $this->request->getPost('observaciones') ?? '';

        if (!$idCliente || !$descripcion) {
            return redirect()->back()->with('error', 'Cliente y descripción son requeridos.');
        }

        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado.');
        }

        $enlaceFinal = null;
        $esEnlaceExterno = false;

        if ($tipoCarga === 'enlace') {
            // Enlace externo (Google Drive, OneDrive, etc.)
            $urlExterna = $this->request->getPost('url_externa');
            if (empty($urlExterna) || !filter_var($urlExterna, FILTER_VALIDATE_URL)) {
                return redirect()->back()->with('error', 'El enlace proporcionado no es válido.');
            }
            $enlaceFinal = $urlExterna;
            $esEnlaceExterno = true;
        } else {
            // Archivo subido
            $archivo = $this->request->getFile('archivo_planilla');

            if (!$archivo || !$archivo->isValid()) {
                return redirect()->back()->with('error', 'Error al subir el archivo. Intente nuevamente.');
            }

            // Validar tipo de archivo
            $tiposPermitidos = [
                'application/pdf',
                'image/jpeg', 'image/png', 'image/jpg',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            ];
            if (!in_array($archivo->getMimeType(), $tiposPermitidos)) {
                return redirect()->back()->with('error', 'Tipo de archivo no permitido. Use PDF, JPG, PNG o Excel.');
            }

            // Validar tamaño (10MB máximo)
            if ($archivo->getSize() > 10 * 1024 * 1024) {
                return redirect()->back()->with('error', 'El archivo excede el tamaño máximo de 10MB.');
            }

            // Crear directorio si no existe
            $carpetaNit = $cliente['nit_cliente'];
            $uploadPath = FCPATH . 'uploads/' . $carpetaNit;
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            // Generar nombre único
            $extension = $archivo->getExtension();
            $nombreArchivo = 'planilla_srl_' . date('Ymd_His') . '.' . $extension;

            // Mover archivo
            if (!$archivo->move($uploadPath, $nombreArchivo)) {
                return redirect()->back()->with('error', 'Error al guardar el archivo en el servidor.');
            }

            $enlaceFinal = base_url('uploads/' . $carpetaNit . '/' . $nombreArchivo);
        }

        // Generar código secuencial para planillas
        $ultimoDoc = $this->db->table('tbl_documentos_sst')
            ->where('id_cliente', $idCliente)
            ->where('tipo_documento', 'planilla_afiliacion_srl')
            ->orderBy('id_documento', 'DESC')
            ->get()
            ->getRowArray();

        $secuencia = 1;
        if ($ultimoDoc && preg_match('/PLA-SRL-(\d{3})/', $ultimoDoc['codigo'], $matches)) {
            $secuencia = intval($matches[1]) + 1;
        }
        $codigo = 'PLA-SRL-' . str_pad($secuencia, 3, '0', STR_PAD_LEFT);

        // Crear registro en tbl_documentos_sst
        $datosDocumento = [
            'id_cliente' => $idCliente,
            'tipo_documento' => 'planilla_afiliacion_srl',
            'codigo' => $codigo,
            'titulo' => $descripcion,
            'anio' => date('Y'),
            'version' => 1,
            'estado' => 'aprobado',
            'contenido' => json_encode([
                'descripcion' => $descripcion,
                'observaciones' => $observaciones,
                'es_enlace_externo' => $esEnlaceExterno,
                'url' => $enlaceFinal
            ]),
            'archivo_pdf' => $esEnlaceExterno ? null : $enlaceFinal,
            'url_externa' => $esEnlaceExterno ? $enlaceFinal : null,
            'observaciones' => $observaciones,
            'fecha_aprobacion' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->table('tbl_documentos_sst')->insert($datosDocumento);
        $idDocumento = $this->db->insertID();

        // Crear versión inicial
        $this->db->table('tbl_doc_versiones_sst')->insert([
            'id_documento' => $idDocumento,
            'codigo' => $codigo,
            'version_texto' => '1.0',
            'tipo_cambio' => 'mayor',
            'descripcion_cambio' => 'Carga inicial de planilla',
            'estado' => 'vigente',
            'archivo_pdf' => $esEnlaceExterno ? null : $enlaceFinal,
            'autorizado_por' => session()->get('nombre') ?? 'Sistema',
            'fecha_autorizacion' => date('Y-m-d H:i:s')
        ]);

        // Publicar en reportList
        $detailReport = $this->db->table('detail_report')
            ->where("detail_report COLLATE utf8mb4_general_ci LIKE '%Documento SG-SST%'", null, false)
            ->get()
            ->getRowArray();
        $idDetailReport = $detailReport['id_detailreport'] ?? 2;

        $tituloReporte = $codigo . ' - ' . $descripcion;
        $obsReporte = 'Planilla de afiliación SRL. ' . ($observaciones ?: 'Sin observaciones.');
        if ($esEnlaceExterno) {
            $obsReporte .= ' (Enlace externo)';
        }

        $this->db->table('tbl_reporte')->insert([
            'titulo_reporte' => $tituloReporte,
            'id_detailreport' => $idDetailReport,
            'id_report_type' => 12, // Reportes SST
            'id_cliente' => $idCliente,
            'enlace' => $enlaceFinal,
            'estado' => 'CERRADO',
            'observaciones' => $obsReporte,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Redirigir a la carpeta
        return redirect()->to('documentacion/carpeta/' . $idCarpeta)
            ->with('success', 'Planilla adjuntada y publicada en Reportes exitosamente.');
    }

    /**
     * Adjuntar soporte de verificación de medidas de prevención y control (4.2.2)
     */
    public function adjuntarSoporteVerificacion()
    {
        $idCliente = $this->request->getPost('id_cliente');
        $idCarpeta = $this->request->getPost('id_carpeta');
        $tipoCarga = $this->request->getPost('tipo_carga'); // 'archivo' o 'enlace'
        $descripcion = $this->request->getPost('descripcion');
        $anio = $this->request->getPost('anio') ?? date('Y');
        $observaciones = $this->request->getPost('observaciones') ?? '';

        if (!$idCliente || !$descripcion) {
            return redirect()->back()->with('error', 'Cliente y descripción son requeridos.');
        }

        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado.');
        }

        $enlaceFinal = null;
        $esEnlaceExterno = false;

        if ($tipoCarga === 'enlace') {
            // Enlace externo (Google Drive, OneDrive, etc.)
            $urlExterna = $this->request->getPost('url_externa');
            if (empty($urlExterna) || !filter_var($urlExterna, FILTER_VALIDATE_URL)) {
                return redirect()->back()->with('error', 'El enlace proporcionado no es válido.');
            }
            $enlaceFinal = $urlExterna;
            $esEnlaceExterno = true;
        } else {
            // Archivo subido
            $archivo = $this->request->getFile('archivo_soporte');

            if (!$archivo || !$archivo->isValid()) {
                return redirect()->back()->with('error', 'Error al subir el archivo. Intente nuevamente.');
            }

            // Validar tipo de archivo
            $tiposPermitidos = [
                'application/pdf',
                'image/jpeg', 'image/png', 'image/jpg',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            ];
            if (!in_array($archivo->getMimeType(), $tiposPermitidos)) {
                return redirect()->back()->with('error', 'Tipo de archivo no permitido. Use PDF, JPG, PNG, Excel o Word.');
            }

            // Validar tamaño (10MB máximo)
            if ($archivo->getSize() > 10 * 1024 * 1024) {
                return redirect()->back()->with('error', 'El archivo excede el tamaño máximo de 10MB.');
            }

            // Crear directorio si no existe
            $carpetaNit = $cliente['nit_cliente'];
            $uploadPath = FCPATH . 'uploads/' . $carpetaNit;
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            // Generar nombre único
            $extension = $archivo->getExtension();
            $nombreArchivo = 'soporte_verificacion_' . date('Ymd_His') . '.' . $extension;

            // Mover archivo
            if (!$archivo->move($uploadPath, $nombreArchivo)) {
                return redirect()->back()->with('error', 'Error al guardar el archivo en el servidor.');
            }

            $enlaceFinal = base_url('uploads/' . $carpetaNit . '/' . $nombreArchivo);
        }

        // Generar código secuencial para soportes de verificación
        $ultimoDoc = $this->db->table('tbl_documentos_sst')
            ->where('id_cliente', $idCliente)
            ->where('tipo_documento', 'soporte_verificacion_medidas')
            ->orderBy('id_documento', 'DESC')
            ->get()
            ->getRowArray();

        $secuencia = 1;
        if ($ultimoDoc && preg_match('/SOP-VMP-(\d{3})/', $ultimoDoc['codigo'], $matches)) {
            $secuencia = intval($matches[1]) + 1;
        }
        $codigo = 'SOP-VMP-' . str_pad($secuencia, 3, '0', STR_PAD_LEFT);

        // Crear registro en tbl_documentos_sst
        $datosDocumento = [
            'id_cliente' => $idCliente,
            'tipo_documento' => 'soporte_verificacion_medidas',
            'codigo' => $codigo,
            'titulo' => $descripcion,
            'anio' => $anio,
            'version' => 1,
            'estado' => 'aprobado',
            'contenido' => json_encode([
                'descripcion' => $descripcion,
                'observaciones' => $observaciones,
                'es_enlace_externo' => $esEnlaceExterno,
                'url' => $enlaceFinal
            ]),
            'archivo_pdf' => $esEnlaceExterno ? null : $enlaceFinal,
            'url_externa' => $esEnlaceExterno ? $enlaceFinal : null,
            'observaciones' => $observaciones,
            'fecha_aprobacion' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->table('tbl_documentos_sst')->insert($datosDocumento);
        $idDocumento = $this->db->insertID();

        // Crear versión inicial
        $this->db->table('tbl_doc_versiones_sst')->insert([
            'id_documento' => $idDocumento,
            'codigo' => $codigo,
            'version_texto' => '1.0',
            'tipo_cambio' => 'mayor',
            'descripcion_cambio' => 'Carga inicial de soporte de verificación',
            'estado' => 'vigente',
            'archivo_pdf' => $esEnlaceExterno ? null : $enlaceFinal,
            'autorizado_por' => session()->get('nombre') ?? 'Sistema',
            'fecha_autorizacion' => date('Y-m-d H:i:s')
        ]);

        // Publicar en reportList
        $detailReport = $this->db->table('detail_report')
            ->where("detail_report COLLATE utf8mb4_general_ci LIKE '%Documento SG-SST%'", null, false)
            ->get()
            ->getRowArray();
        $idDetailReport = $detailReport['id_detailreport'] ?? 2;

        $tituloReporte = $codigo . ' - ' . $descripcion . ' (' . $anio . ')';
        $obsReporte = 'Soporte verificación medidas prevención. ' . ($observaciones ?: 'Sin observaciones.');
        if ($esEnlaceExterno) {
            $obsReporte .= ' (Enlace externo)';
        }

        $this->db->table('tbl_reporte')->insert([
            'titulo_reporte' => $tituloReporte,
            'id_detailreport' => $idDetailReport,
            'id_report_type' => 12, // Reportes SST
            'id_cliente' => $idCliente,
            'enlace' => $enlaceFinal,
            'estado' => 'CERRADO',
            'observaciones' => $obsReporte,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Redirigir a la carpeta
        return redirect()->to('documentacion/carpeta/' . $idCarpeta)
            ->with('success', 'Soporte de verificación adjuntado y publicado exitosamente.');
    }

    /**
     * Adjuntar soporte de planificación de auditorías con el COPASST (6.1.4)
     */
    public function adjuntarSoporteAuditoria()
    {
        $idCliente = $this->request->getPost('id_cliente');
        $idCarpeta = $this->request->getPost('id_carpeta');
        $tipoCarga = $this->request->getPost('tipo_carga'); // 'archivo' o 'enlace'
        $descripcion = $this->request->getPost('descripcion');
        $anio = $this->request->getPost('anio') ?? date('Y');
        $observaciones = $this->request->getPost('observaciones') ?? '';

        if (!$idCliente || !$descripcion) {
            return redirect()->back()->with('error', 'Cliente y descripción son requeridos.');
        }

        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado.');
        }

        $enlaceFinal = null;
        $esEnlaceExterno = false;

        if ($tipoCarga === 'enlace') {
            // Enlace externo (Google Drive, OneDrive, etc.)
            $urlExterna = $this->request->getPost('url_externa');
            if (empty($urlExterna) || !filter_var($urlExterna, FILTER_VALIDATE_URL)) {
                return redirect()->back()->with('error', 'El enlace proporcionado no es válido.');
            }
            $enlaceFinal = $urlExterna;
            $esEnlaceExterno = true;
        } else {
            // Archivo subido
            $archivo = $this->request->getFile('archivo_soporte');

            if (!$archivo || !$archivo->isValid()) {
                return redirect()->back()->with('error', 'Error al subir el archivo. Intente nuevamente.');
            }

            // Validar tipo de archivo
            $tiposPermitidos = [
                'application/pdf',
                'image/jpeg', 'image/png', 'image/jpg',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            ];
            if (!in_array($archivo->getMimeType(), $tiposPermitidos)) {
                return redirect()->back()->with('error', 'Tipo de archivo no permitido. Use PDF, JPG, PNG, Excel o Word.');
            }

            // Validar tamaño (10MB máximo)
            if ($archivo->getSize() > 10 * 1024 * 1024) {
                return redirect()->back()->with('error', 'El archivo excede el tamaño máximo de 10MB.');
            }

            // Crear directorio si no existe
            $carpetaNit = $cliente['nit_cliente'];
            $uploadPath = FCPATH . 'uploads/' . $carpetaNit;
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            // Generar nombre único
            $extension = $archivo->getExtension();
            $nombreArchivo = 'soporte_auditoria_' . date('Ymd_His') . '.' . $extension;

            // Mover archivo
            if (!$archivo->move($uploadPath, $nombreArchivo)) {
                return redirect()->back()->with('error', 'Error al guardar el archivo en el servidor.');
            }

            $enlaceFinal = base_url('uploads/' . $carpetaNit . '/' . $nombreArchivo);
        }

        // Generar código secuencial para soportes de auditoría
        $ultimoDoc = $this->db->table('tbl_documentos_sst')
            ->where('id_cliente', $idCliente)
            ->where('tipo_documento', 'soporte_planificacion_auditoria')
            ->orderBy('id_documento', 'DESC')
            ->get()
            ->getRowArray();

        $secuencia = 1;
        if ($ultimoDoc && preg_match('/SOP-AUD-(\d{3})/', $ultimoDoc['codigo'], $matches)) {
            $secuencia = intval($matches[1]) + 1;
        }
        $codigo = 'SOP-AUD-' . str_pad($secuencia, 3, '0', STR_PAD_LEFT);

        // Crear registro en tbl_documentos_sst
        $datosDocumento = [
            'id_cliente' => $idCliente,
            'tipo_documento' => 'soporte_planificacion_auditoria',
            'codigo' => $codigo,
            'titulo' => $descripcion,
            'anio' => $anio,
            'version' => 1,
            'estado' => 'aprobado',
            'contenido' => json_encode([
                'descripcion' => $descripcion,
                'observaciones' => $observaciones,
                'es_enlace_externo' => $esEnlaceExterno,
                'url' => $enlaceFinal
            ]),
            'archivo_pdf' => $esEnlaceExterno ? null : $enlaceFinal,
            'url_externa' => $esEnlaceExterno ? $enlaceFinal : null,
            'observaciones' => $observaciones,
            'fecha_aprobacion' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->table('tbl_documentos_sst')->insert($datosDocumento);
        $idDocumento = $this->db->insertID();

        // Crear versión inicial
        $this->db->table('tbl_doc_versiones_sst')->insert([
            'id_documento' => $idDocumento,
            'codigo' => $codigo,
            'version_texto' => '1.0',
            'tipo_cambio' => 'mayor',
            'descripcion_cambio' => 'Carga inicial de soporte de auditoría',
            'estado' => 'vigente',
            'archivo_pdf' => $esEnlaceExterno ? null : $enlaceFinal,
            'autorizado_por' => session()->get('nombre') ?? 'Sistema',
            'fecha_autorizacion' => date('Y-m-d H:i:s')
        ]);

        // Publicar en reportList
        $detailReport = $this->db->table('detail_report')
            ->where("detail_report COLLATE utf8mb4_general_ci LIKE '%Documento SG-SST%'", null, false)
            ->get()
            ->getRowArray();
        $idDetailReport = $detailReport['id_detailreport'] ?? 2;

        $tituloReporte = $codigo . ' - ' . $descripcion . ' (' . $anio . ')';
        $obsReporte = 'Soporte planificación auditoría COPASST. ' . ($observaciones ?: 'Sin observaciones.');
        if ($esEnlaceExterno) {
            $obsReporte .= ' (Enlace externo)';
        }

        $this->db->table('tbl_reporte')->insert([
            'titulo_reporte' => $tituloReporte,
            'id_detailreport' => $idDetailReport,
            'id_report_type' => 12, // Reportes SST
            'id_cliente' => $idCliente,
            'enlace' => $enlaceFinal,
            'estado' => 'CERRADO',
            'observaciones' => $obsReporte,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Redirigir a la carpeta
        return redirect()->to('documentacion/carpeta/' . $idCarpeta)
            ->with('success', 'Soporte de auditoría adjuntado y publicado exitosamente.');
    }

    /**
     * Adjuntar soporte de entrega de EPP (4.2.6)
     */
    public function adjuntarSoporteEPP()
    {
        return $this->adjuntarSoporteGenerico(
            'soporte_entrega_epp',
            'SOP-EPP',
            'soporte_epp_',
            'Soporte entrega EPP',
            'Soporte de entrega de EPP adjuntado exitosamente.'
        );
    }

    /**
     * Adjuntar soporte de plan de emergencias (5.1.1)
     */
    public function adjuntarSoporteEmergencias()
    {
        return $this->adjuntarSoporteGenerico(
            'soporte_plan_emergencias',
            'SOP-EME',
            'soporte_emergencias_',
            'Soporte plan emergencias',
            'Soporte de plan de emergencias adjuntado exitosamente.'
        );
    }

    /**
     * Adjuntar soporte de brigada de emergencias (5.1.2)
     */
    public function adjuntarSoporteBrigada()
    {
        return $this->adjuntarSoporteGenerico(
            'soporte_brigada_emergencias',
            'SOP-BRI',
            'soporte_brigada_',
            'Soporte brigada emergencias',
            'Soporte de brigada adjuntado exitosamente.'
        );
    }

    /**
     * Adjuntar soporte de revisión por la dirección (6.1.3)
     */
    public function adjuntarSoporteRevision()
    {
        return $this->adjuntarSoporteGenerico(
            'soporte_revision_direccion',
            'SOP-REV',
            'soporte_revision_',
            'Soporte revisión dirección',
            'Soporte de revisión por la dirección adjuntado exitosamente.'
        );
    }

    /**
     * Adjuntar soporte de agua y servicios sanitarios (3.1.8)
     */
    public function adjuntarSoporteAgua()
    {
        return $this->adjuntarSoporteGenerico(
            'soporte_agua_servicios',
            'SOP-SAN',
            'soporte_agua_',
            'Soporte agua y servicios sanitarios',
            'Soporte de agua y servicios sanitarios adjuntado exitosamente.'
        );
    }

    /**
     * Adjuntar soporte de eliminación de residuos (3.1.9)
     */
    public function adjuntarSoporteResiduos()
    {
        return $this->adjuntarSoporteGenerico(
            'soporte_eliminacion_residuos',
            'SOP-RES',
            'soporte_residuos_',
            'Soporte eliminación residuos',
            'Soporte de eliminación de residuos adjuntado exitosamente.'
        );
    }

    /**
     * Adjuntar soporte de mediciones ambientales (4.1.4)
     */
    public function adjuntarSoporteMediciones()
    {
        return $this->adjuntarSoporteGenerico(
            'soporte_mediciones_ambientales',
            'SOP-MED',
            'soporte_mediciones_',
            'Soporte mediciones ambientales',
            'Soporte de mediciones ambientales adjuntado exitosamente.'
        );
    }

    /**
     * Adjuntar soporte de medidas de prevención y control (4.2.1)
     */
    public function adjuntarSoporteMedidasControl()
    {
        return $this->adjuntarSoporteGenerico(
            'soporte_medidas_prevencion_control',
            'SOP-MPC',
            'soporte_medidas_control_',
            'Soporte medidas prevención y control',
            'Soporte de medidas de prevención y control adjuntado exitosamente.'
        );
    }

    /**
     * Adjuntar soporte de diagnóstico de condiciones de salud (3.1.1)
     */
    public function adjuntarSoporteDiagnosticoSalud()
    {
        return $this->adjuntarSoporteGenerico(
            'soporte_diagnostico_salud',
            'SOP-DCS',
            'soporte_diagnostico_salud_',
            'Soporte diagnóstico condiciones salud',
            'Soporte de diagnóstico de condiciones de salud adjuntado exitosamente.'
        );
    }

    /**
     * Adjuntar soporte de información al médico perfiles de cargo (3.1.3)
     */
    public function adjuntarSoportePerfilesMedico()
    {
        return $this->adjuntarSoporteGenerico(
            'soporte_perfiles_medico',
            'SOP-IMP',
            'soporte_perfiles_medico_',
            'Soporte información médico perfiles',
            'Soporte de información al médico adjuntado exitosamente.'
        );
    }

    /**
     * Adjuntar soporte de evaluaciones médicas ocupacionales (3.1.4)
     */
    public function adjuntarSoporteEvaluacionesMedicas()
    {
        return $this->adjuntarSoporteGenerico(
            'soporte_evaluaciones_medicas',
            'SOP-EMO',
            'soporte_evaluaciones_medicas_',
            'Soporte evaluaciones médicas ocupacionales',
            'Soporte de evaluaciones médicas adjuntado exitosamente.'
        );
    }

    /**
     * Adjuntar soporte de custodia de historias clínicas (3.1.5)
     */
    public function adjuntarSoporteCustodiaHC()
    {
        return $this->adjuntarSoporteGenerico(
            'soporte_custodia_hc',
            'SOP-CHC',
            'soporte_custodia_hc_',
            'Soporte custodia historias clínicas',
            'Soporte de custodia de historias clínicas adjuntado exitosamente.'
        );
    }

    /**
     * Adjuntar soporte de curso 50 horas (1.2.3)
     */
    public function adjuntarSoporteCurso50h()
    {
        return $this->adjuntarSoporteGenerico(
            'soporte_curso_50h',
            'SOP-C50',
            'soporte_curso_50h_',
            'Certificado curso 50 horas SST',
            'Certificado del curso de 50 horas adjuntado exitosamente.'
        );
    }

    /**
     * Adjuntar soporte de evaluación de prioridades (2.3.1)
     */
    public function adjuntarSoporteEvaluacionPrioridades()
    {
        return $this->adjuntarSoporteGenerico(
            'soporte_evaluacion_prioridades',
            'SOP-EVP',
            'soporte_evaluacion_prioridades_',
            'Soporte evaluación e identificación de prioridades',
            'Soporte de evaluación de prioridades adjuntado exitosamente.'
        );
    }

    /**
     * Adjuntar soporte de plan de objetivos y metas (2.4.1)
     */
    public function adjuntarSoportePlanObjetivos()
    {
        return $this->adjuntarSoporteGenerico(
            'soporte_plan_objetivos',
            'SOP-POM',
            'soporte_plan_objetivos_',
            'Soporte plan objetivos, metas, recursos',
            'Soporte del plan de objetivos y metas adjuntado exitosamente.'
        );
    }

    /**
     * Adjuntar soporte de rendición sobre el desempeño (2.6.1)
     */
    public function adjuntarSoporteRendicion()
    {
        return $this->adjuntarSoporteGenerico(
            'soporte_rendicion_desempeno',
            'SOP-RDD',
            'soporte_rendicion_',
            'Soporte rendición sobre el desempeño',
            'Soporte de rendición de cuentas adjuntado exitosamente.'
        );
    }

    /**
     * Adjuntar soporte de conformación COPASST (1.1.6)
     */
    public function adjuntarSoporteCopasst()
    {
        return $this->adjuntarSoporteGenerico(
            'soporte_conformacion_copasst',
            'SOP-COP',
            'soporte_copasst_',
            'Soporte conformación COPASST',
            'Soporte de conformación COPASST adjuntado exitosamente.'
        );
    }

    /**
     * Adjuntar soporte de comité de convivencia (1.1.8)
     */
    public function adjuntarSoporteConvivencia()
    {
        return $this->adjuntarSoporteGenerico(
            'soporte_comite_convivencia',
            'SOP-CCV',
            'soporte_convivencia_',
            'Soporte comité de convivencia',
            'Soporte del comité de convivencia adjuntado exitosamente.'
        );
    }

    /**
     * Adjuntar soporte de Promoción y Prevención en Salud (3.1.2)
     */
    public function adjuntarSoportePypSalud()
    {
        return $this->adjuntarSoporteGenerico(
            'soporte_pyp_salud',
            'SOP-PYP',
            'soporte_pyp_salud_',
            'Soporte PyP Salud',
            'Soporte de Promoción y Prevención en Salud adjuntado exitosamente.'
        );
    }

    /**
     * Adjuntar soporte de Inducción y Reinducción (1.2.2)
     */
    public function adjuntarSoporteInduccion()
    {
        return $this->adjuntarSoporteGenerico(
            'soporte_induccion',
            'SOP-IND',
            'soporte_induccion_',
            'Soporte Inducción Reinducción',
            'Soporte de Inducción y Reinducción adjuntado exitosamente.'
        );
    }

    /**
     * Adjuntar soporte de Matriz Legal (2.7.1)
     */
    public function adjuntarSoporteMatrizLegal()
    {
        return $this->adjuntarSoporteGenerico(
            'soporte_matriz_legal',
            'SOP-MRL',
            'soporte_matriz_legal_',
            'Soporte Matriz Legal',
            'Soporte de Matriz Legal adjuntado exitosamente.'
        );
    }

    /**
     * Método genérico para adjuntar soportes (reutilizable)
     */
    protected function adjuntarSoporteGenerico(
        string $tipoDocumento,
        string $prefijoCode,
        string $prefijoArchivo,
        string $descripcionReporte,
        string $mensajeExito
    ) {
        $idCliente = $this->request->getPost('id_cliente');
        $idCarpeta = $this->request->getPost('id_carpeta');
        $tipoCarga = $this->request->getPost('tipo_carga');
        $descripcion = $this->request->getPost('descripcion');
        $anio = $this->request->getPost('anio') ?? date('Y');
        $observaciones = $this->request->getPost('observaciones') ?? '';

        if (!$idCliente || !$descripcion) {
            return redirect()->back()->with('error', 'Cliente y descripción son requeridos.');
        }

        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado.');
        }

        $enlaceFinal = null;
        $esEnlaceExterno = false;

        if ($tipoCarga === 'enlace') {
            $urlExterna = $this->request->getPost('url_externa');
            if (empty($urlExterna) || !filter_var($urlExterna, FILTER_VALIDATE_URL)) {
                return redirect()->back()->with('error', 'El enlace proporcionado no es válido.');
            }
            $enlaceFinal = $urlExterna;
            $esEnlaceExterno = true;
        } else {
            $archivo = $this->request->getFile('archivo_soporte');
            if (!$archivo || !$archivo->isValid()) {
                return redirect()->back()->with('error', 'Error al subir el archivo.');
            }

            $tiposPermitidos = [
                'application/pdf',
                'image/jpeg', 'image/png', 'image/jpg',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            ];
            if (!in_array($archivo->getMimeType(), $tiposPermitidos)) {
                return redirect()->back()->with('error', 'Tipo de archivo no permitido.');
            }

            if ($archivo->getSize() > 10 * 1024 * 1024) {
                return redirect()->back()->with('error', 'El archivo excede 10MB.');
            }

            $carpetaNit = $cliente['nit_cliente'];
            $uploadPath = FCPATH . 'uploads/' . $carpetaNit;
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $extension = $archivo->getExtension();
            $nombreArchivo = $prefijoArchivo . date('Ymd_His') . '.' . $extension;

            if (!$archivo->move($uploadPath, $nombreArchivo)) {
                return redirect()->back()->with('error', 'Error al guardar el archivo.');
            }

            $enlaceFinal = base_url('uploads/' . $carpetaNit . '/' . $nombreArchivo);
        }

        // Generar código secuencial
        $ultimoDoc = $this->db->table('tbl_documentos_sst')
            ->where('id_cliente', $idCliente)
            ->where('tipo_documento', $tipoDocumento)
            ->orderBy('id_documento', 'DESC')
            ->get()
            ->getRowArray();

        $secuencia = 1;
        if ($ultimoDoc && preg_match('/' . $prefijoCode . '-(\d{3})/', $ultimoDoc['codigo'], $matches)) {
            $secuencia = intval($matches[1]) + 1;
        }
        $codigo = $prefijoCode . '-' . str_pad($secuencia, 3, '0', STR_PAD_LEFT);

        // Crear documento
        $this->db->table('tbl_documentos_sst')->insert([
            'id_cliente' => $idCliente,
            'tipo_documento' => $tipoDocumento,
            'codigo' => $codigo,
            'titulo' => $descripcion,
            'anio' => $anio,
            'version' => 1,
            'estado' => 'aprobado',
            'contenido' => json_encode(['descripcion' => $descripcion, 'observaciones' => $observaciones]),
            'archivo_pdf' => $esEnlaceExterno ? null : $enlaceFinal,
            'url_externa' => $esEnlaceExterno ? $enlaceFinal : null,
            'observaciones' => $observaciones,
            'fecha_aprobacion' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        $idDocumento = $this->db->insertID();

        // Crear versión
        $this->db->table('tbl_doc_versiones_sst')->insert([
            'id_documento' => $idDocumento,
            'codigo' => $codigo,
            'version_texto' => '1.0',
            'tipo_cambio' => 'mayor',
            'descripcion_cambio' => 'Carga inicial',
            'estado' => 'vigente',
            'archivo_pdf' => $esEnlaceExterno ? null : $enlaceFinal,
            'autorizado_por' => session()->get('nombre') ?? 'Sistema',
            'fecha_autorizacion' => date('Y-m-d H:i:s')
        ]);

        // Publicar en reportList
        $detailReport = $this->db->table('detail_report')
            ->where("detail_report COLLATE utf8mb4_general_ci LIKE '%Documento SG-SST%'", null, false)
            ->get()
            ->getRowArray();
        $idDetailReport = $detailReport['id_detailreport'] ?? 2;

        $this->db->table('tbl_reporte')->insert([
            'titulo_reporte' => $codigo . ' - ' . $descripcion . ' (' . $anio . ')',
            'id_detailreport' => $idDetailReport,
            'id_report_type' => 12,
            'id_cliente' => $idCliente,
            'enlace' => $enlaceFinal,
            'estado' => 'CERRADO',
            'observaciones' => $descripcionReporte . '. ' . ($observaciones ?: 'Sin observaciones.'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return redirect()->to('documentacion/carpeta/' . $idCarpeta)->with('success', $mensajeExito);
    }

    /**
     * Exporta el documento a Word (.doc) usando HTML compatible
     */
    public function exportarWord(int $idDocumento)
    {
        $documento = $this->db->table('tbl_documentos_sst')
            ->where('id_documento', $idDocumento)
            ->get()
            ->getRowArray();

        if (!$documento) {
            return redirect()->back()->with('error', 'Documento no encontrado');
        }

        $cliente = $this->clienteModel->find($documento['id_cliente']);
        $contenido = json_decode($documento['contenido'], true);

        // Normalizar secciones
        if (!empty($contenido['secciones'])) {
            $contenido['secciones'] = $this->normalizarSecciones($contenido['secciones'], $documento['tipo_documento']);
        }

        // Preparar logo como base64
        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoData = file_get_contents($logoPath);
                $logoMime = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode($logoData);
            }
        }

        // Obtener versiones del documento
        $versiones = $this->db->table('tbl_doc_versiones_sst')
            ->where('id_documento', $idDocumento)
            ->orderBy('version', 'DESC')
            ->orderBy('version_texto', 'DESC')
            ->get()
            ->getResultArray();

        // Obtener contexto SST
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($documento['id_cliente']);

        // Obtener datos del consultor asignado
        $consultor = null;
        $firmaConsultorBase64 = '';
        $idConsultor = $contexto['id_consultor_responsable'] ?? $cliente['id_consultor'] ?? null;
        if ($idConsultor) {
            $consultorModel = new \App\Models\ConsultantModel();
            $consultor = $consultorModel->find($idConsultor);

            // Preparar firma del consultor como base64 para Word
            if (!empty($consultor['firma_consultor'])) {
                $firmaPath = FCPATH . 'uploads/' . $consultor['firma_consultor'];
                if (file_exists($firmaPath)) {
                    $firmaData = file_get_contents($firmaPath);
                    $firmaMime = mime_content_type($firmaPath);
                    $firmaConsultorBase64 = 'data:' . $firmaMime . ';base64,' . base64_encode($firmaData);
                }
            }
        }

        $data = [
            'titulo' => $documento['titulo'],
            'cliente' => $cliente,
            'documento' => $documento,
            'contenido' => $contenido,
            'anio' => $documento['anio'],
            'logoBase64' => $logoBase64,
            'versiones' => $versiones,
            'contexto' => $contexto,
            'consultor' => $consultor,
            'firmaConsultorBase64' => $firmaConsultorBase64,
            // Firmantes desde servicio (arquitectura escalable) - igual que PDF
            'firmantesDefinidos' => $this->configService->obtenerFirmantes($documento['tipo_documento'])
        ];

        // Renderizar la vista HTML para Word
        $html = view('documentos_sst/word_template', $data);

        // Nombre del archivo
        $nombreArchivo = ($documento['codigo'] ?? 'documento') . '_' . url_title($documento['titulo'], '-', true) . '.doc';

        // Headers para descarga como Word
        header('Content-Type: application/msword');
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
        header('Cache-Control: max-age=0');

        echo $html;
        exit;
    }

    /**
     * Aprueba el documento completo y crea una nueva version
     * Usa el servicio centralizado DocumentoVersionService para garantizar consistencia
     */
    public function aprobarDocumento()
    {
        $idDocumento = $this->request->getPost('id_documento');
        $tipoCambio = $this->request->getPost('tipo_cambio') ?? 'menor';
        $descripcionCambio = $this->request->getPost('descripcion_cambio');

        if (empty($idDocumento)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID de documento requerido'
            ]);
        }

        // Obtener usuario actual
        $session = session();
        $usuarioId = $session->get('id_usuario') ?? 0;
        $usuarioNombre = $session->get('nombre_usuario') ?? 'Usuario del sistema';

        // Usar el servicio centralizado de versiones
        $resultado = $this->versionService->aprobarVersion(
            (int)$idDocumento,
            (int)$usuarioId,
            $usuarioNombre,
            $descripcionCambio,
            $tipoCambio
        );

        // Adaptar respuesta para mantener compatibilidad con frontend existente
        if ($resultado['success'] && isset($resultado['data'])) {
            return $this->response->setJSON([
                'success' => true,
                'message' => $resultado['message'],
                'version' => $resultado['data']['version_texto'],
                'id_version' => $resultado['data']['id_version']
            ]);
        }

        return $this->response->setJSON($resultado);
    }

    /**
     * Inicia el proceso de nueva version: cambia estado a borrador y redirige a edicion
     * Usa el servicio centralizado DocumentoVersionService para garantizar consistencia
     */
    public function iniciarNuevaVersion()
    {
        $idDocumento = $this->request->getPost('id_documento');
        $tipoCambio = $this->request->getPost('tipo_cambio') ?? 'menor';
        $descripcionCambio = $this->request->getPost('descripcion_cambio');

        if (empty($idDocumento) || empty($descripcionCambio)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Faltan datos requeridos (id_documento y descripcion_cambio son obligatorios)'
            ]);
        }

        // Usar el servicio centralizado de versiones
        $resultado = $this->versionService->iniciarNuevaVersion(
            (int)$idDocumento,
            $tipoCambio,
            $descripcionCambio
        );

        // Adaptar respuesta para mantener compatibilidad con frontend existente
        if ($resultado['success'] && isset($resultado['data'])) {
            return $this->response->setJSON([
                'success' => true,
                'message' => $resultado['message'],
                'proxima_version' => $resultado['data']['proxima_version'],
                'tipo_cambio' => $resultado['data']['tipo_cambio'],
                'redirect_url' => $resultado['data']['url_edicion']
            ]);
        }

        return $this->response->setJSON($resultado);
    }

    /**
     * Restaura una version anterior del documento
     * Usa el servicio centralizado DocumentoVersionService
     */
    public function restaurarVersion()
    {
        $idDocumento = $this->request->getPost('id_documento');
        $idVersion = $this->request->getPost('id_version');

        if (empty($idDocumento) || empty($idVersion)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Faltan datos requeridos'
            ]);
        }

        $session = session();
        $usuarioId = $session->get('id_usuario') ?? 0;
        $usuarioNombre = $session->get('nombre_usuario') ?? 'Usuario del sistema';

        $resultado = $this->versionService->restaurarVersion(
            (int)$idDocumento,
            (int)$idVersion,
            (int)$usuarioId,
            $usuarioNombre
        );

        return $this->response->setJSON($resultado);
    }

    /**
     * Cancela la edicion de una nueva version y restaura el estado anterior
     */
    public function cancelarNuevaVersion()
    {
        $idDocumento = $this->request->getPost('id_documento');

        if (empty($idDocumento)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID de documento requerido'
            ]);
        }

        $resultado = $this->versionService->cancelarNuevaVersion((int)$idDocumento);
        return $this->response->setJSON($resultado);
    }

    /**
     * Obtiene el historial de versiones del documento
     */
    public function historialVersiones(int $idDocumento)
    {
        $versiones = $this->db->table('tbl_doc_versiones_sst')
            ->where('id_documento', $idDocumento)
            ->orderBy('fecha_autorizacion', 'DESC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'success' => true,
            'versiones' => $versiones
        ]);
    }

    /**
     * Descarga el PDF de una version especifica
     */
    public function descargarVersionPDF(int $idVersion)
    {
        $version = $this->db->table('tbl_doc_versiones_sst v')
            ->select('v.*, d.id_cliente, d.codigo, d.titulo, d.tipo_documento, d.anio')
            ->join('tbl_documentos_sst d', 'd.id_documento = v.id_documento')
            ->where('v.id_version', $idVersion)
            ->get()
            ->getRowArray();

        if (!$version) {
            return redirect()->back()->with('error', 'Version no encontrada');
        }

        $cliente = $this->clienteModel->find($version['id_cliente']);
        $contenido = json_decode($version['contenido_snapshot'], true);

        // Normalizar secciones
        if (!empty($contenido['secciones'])) {
            $contenido['secciones'] = $this->normalizarSecciones($contenido['secciones'], $version['tipo_documento']);
        }

        // Preparar logo como base64
        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoData = file_get_contents($logoPath);
                $logoMime = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode($logoData);
            }
        }

        // Obtener versiones hasta esta version
        $versiones = $this->db->table('tbl_doc_versiones_sst')
            ->where('id_documento', $version['id_documento'])
            ->where('fecha_autorizacion <=', $version['fecha_autorizacion'])
            ->orderBy('fecha_autorizacion', 'ASC')
            ->get()
            ->getResultArray();

        // Obtener responsables del cliente
        $responsableModel = new ResponsableSSTModel();
        $responsables = $responsableModel->getByCliente($version['id_cliente']);

        // Obtener contexto SST
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($version['id_cliente']);

        // Obtener datos del consultor asignado
        $consultor = null;
        $firmaConsultorBase64 = '';
        $idConsultor = $contexto['id_consultor_responsable'] ?? $cliente['id_consultor'] ?? null;
        if ($idConsultor) {
            $consultorModel = new \App\Models\ConsultantModel();
            $consultor = $consultorModel->find($idConsultor);

            // Preparar firma del consultor como base64 para PDF
            if (!empty($consultor['firma_consultor'])) {
                $firmaPath = FCPATH . 'uploads/' . $consultor['firma_consultor'];
                if (file_exists($firmaPath)) {
                    $firmaData = file_get_contents($firmaPath);
                    $firmaMime = mime_content_type($firmaPath);
                    $firmaConsultorBase64 = 'data:' . $firmaMime . ';base64,' . base64_encode($firmaData);
                }
            }
        }

        $data = [
            'titulo' => $version['titulo'],
            'cliente' => $cliente,
            'documento' => [
                'codigo' => $version['codigo'],
                'version' => $version['version'],
                'created_at' => $version['fecha_autorizacion'],
                'estado' => 'aprobado'
            ],
            'contenido' => $contenido,
            'anio' => $version['anio'],
            'logoBase64' => $logoBase64,
            'esVersionHistorica' => true,
            'versionTexto' => $version['version_texto'],
            'versiones' => $versiones,
            'responsables' => $responsables,
            'contexto' => $contexto,
            'consultor' => $consultor,
            'firmaConsultorBase64' => $firmaConsultorBase64
        ];

        $html = view('documentos_sst/pdf_template', $data);

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $nombreArchivo = $version['codigo'] . '_v' . $version['version_texto'] . '_' . url_title($version['titulo'], '-', true) . '.pdf';

        $dompdf->stream($nombreArchivo, ['Attachment' => true]);
    }

    /**
     * Muestra el Procedimiento de Control Documental generado
     */
    public function procedimientoControlDocumental(int $idCliente, int $anio)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $documento = $this->db->table('tbl_documentos_sst')
            ->where('id_cliente', $idCliente)
            ->where('tipo_documento', 'procedimiento_control_documental')
            ->where('anio', $anio)
            ->get()
            ->getRowArray();

        if (!$documento) {
            return redirect()->to(base_url('documentos/generar/procedimiento_control_documental/' . $idCliente))
                ->with('error', 'Documento no encontrado. Genere primero el Procedimiento de Control Documental.');
        }

        $contenido = json_decode($documento['contenido'], true);

        // Normalizar secciones para eliminar duplicados
        if (!empty($contenido['secciones'])) {
            $contenido['secciones'] = $this->normalizarSecciones($contenido['secciones'], 'procedimiento_control_documental');
        }

        // Obtener historial de versiones para la tabla de Control de Cambios
        $versiones = $this->db->table('tbl_doc_versiones_sst')
            ->where('id_documento', $documento['id_documento'])
            ->orderBy('fecha_autorizacion', 'ASC')
            ->get()
            ->getResultArray();

        // Obtener responsables del cliente para las firmas
        $responsableModel = new ResponsableSSTModel();
        $responsables = $responsableModel->getByCliente($idCliente);

        // Obtener contexto SST para datos adicionales
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);

        // Obtener datos del consultor asignado
        $consultor = null;
        $idConsultor = $contexto['id_consultor_responsable'] ?? $cliente['id_consultor'] ?? null;
        if ($idConsultor) {
            $consultorModel = new \App\Models\ConsultantModel();
            $consultor = $consultorModel->find($idConsultor);
        }

        // Obtener firmas electrónicas del documento
        $firmasElectronicas = [];
        $solicitudesFirma = $this->db->table('tbl_doc_firma_solicitudes')
            ->where('id_documento', $documento['id_documento'])
            ->where('estado', 'firmado')
            ->get()
            ->getResultArray();

        foreach ($solicitudesFirma as $sol) {
            $evidencia = $this->db->table('tbl_doc_firma_evidencias')
                ->where('id_solicitud', $sol['id_solicitud'])
                ->get()
                ->getRowArray();
            $firmasElectronicas[$sol['firmante_tipo']] = [
                'solicitud' => $sol,
                'evidencia' => $evidencia
            ];
        }

        // Obtener listado maestro de documentos para la sección 13
        $listadoMaestro = $this->db->table('tbl_documentos_sst d')
            ->select('d.id_documento, d.codigo, d.titulo, d.tipo_documento, d.version, d.estado, d.created_at, d.updated_at')
            ->where('d.id_cliente', $idCliente)
            ->whereIn('d.estado', ['aprobado', 'firmado', 'generado'])
            ->orderBy('d.codigo', 'ASC')
            ->get()
            ->getResultArray();

        // Obtener tipos de documentos del sistema para la sección 6
        $tiposDocumento = $this->db->table('tbl_doc_tipos')
            ->where('activo', 1)
            ->orderBy('id_tipo')
            ->get()
            ->getResultArray();

        // Obtener plantillas para la sección 7 (Codificación)
        $plantillas = $this->db->table('tbl_doc_plantillas')
            ->select('codigo_sugerido, nombre, tipo_documento')
            ->where('activo', 1)
            ->where('tipo_documento IS NOT NULL')
            ->orderBy('codigo_sugerido')
            ->get()
            ->getResultArray();

        $data = [
            'titulo' => 'Procedimiento de Control Documental - ' . $cliente['nombre_cliente'],
            'cliente' => $cliente,
            'documento' => $documento,
            'contenido' => $contenido,
            'anio' => $anio,
            'versiones' => $versiones,
            'responsables' => $responsables,
            'contexto' => $contexto,
            'consultor' => $consultor,
            'firmasElectronicas' => $firmasElectronicas,
            'listadoMaestro' => $listadoMaestro,
            'tiposDocumento' => $tiposDocumento,
            'plantillas' => $plantillas,
            // Firmantes desde servicio (arquitectura escalable)
            'firmantesDefinidos' => $this->configService->obtenerFirmantes('procedimiento_control_documental')
        ];

        return view('documentos_sst/procedimiento_control_documental', $data);
    }

    /**
     * Muestra el Procedimiento de Matriz Legal (2.7.1)
     */
    public function procedimientoMatrizLegal(int $idCliente, int $anio)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $documento = $this->db->table('tbl_documentos_sst')
            ->where('id_cliente', $idCliente)
            ->where('tipo_documento', 'procedimiento_matriz_legal')
            ->where('anio', $anio)
            ->get()
            ->getRowArray();

        if (!$documento) {
            return redirect()->to(base_url('documentos/generar/procedimiento_matriz_legal/' . $idCliente))
                ->with('error', 'Documento no encontrado. Genere primero el Procedimiento de Matriz Legal.');
        }

        $contenido = json_decode($documento['contenido'], true);

        // Normalizar secciones para eliminar duplicados
        if (!empty($contenido['secciones'])) {
            $contenido['secciones'] = $this->normalizarSecciones($contenido['secciones'], 'procedimiento_matriz_legal');
        }

        // Obtener historial de versiones para la tabla de Control de Cambios
        $versiones = $this->db->table('tbl_doc_versiones_sst')
            ->where('id_documento', $documento['id_documento'])
            ->orderBy('fecha_autorizacion', 'ASC')
            ->get()
            ->getResultArray();

        // Obtener responsables del cliente para las firmas
        $responsableModel = new ResponsableSSTModel();
        $responsables = $responsableModel->getByCliente($idCliente);

        // Obtener contexto SST para datos adicionales
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);

        // Obtener datos del consultor asignado
        $consultor = null;
        $idConsultor = $contexto['id_consultor_responsable'] ?? $cliente['id_consultor'] ?? null;
        if ($idConsultor) {
            $consultorModel = new \App\Models\ConsultantModel();
            $consultor = $consultorModel->find($idConsultor);
        }

        // Obtener firmas electrónicas del documento
        $firmasElectronicas = [];
        $solicitudesFirma = $this->db->table('tbl_doc_firma_solicitudes')
            ->where('id_documento', $documento['id_documento'])
            ->where('estado', 'firmado')
            ->get()
            ->getResultArray();

        foreach ($solicitudesFirma as $sol) {
            $evidencia = $this->db->table('tbl_doc_firma_evidencias')
                ->where('id_solicitud', $sol['id_solicitud'])
                ->get()
                ->getRowArray();
            $firmasElectronicas[$sol['firmante_tipo']] = [
                'solicitud' => $sol,
                'evidencia' => $evidencia
            ];
        }

        $data = [
            'titulo' => 'Procedimiento de Identificación de Requisitos Legales - ' . $cliente['nombre_cliente'],
            'cliente' => $cliente,
            'documento' => $documento,
            'contenido' => $contenido,
            'anio' => $anio,
            'versiones' => $versiones,
            'responsables' => $responsables,
            'contexto' => $contexto,
            'consultor' => $consultor,
            'firmasElectronicas' => $firmasElectronicas,
            'firmantesDefinidos' => $this->configService->obtenerFirmantes('procedimiento_matriz_legal')
        ];

        return view('documentos_sst/procedimiento_matriz_legal', $data);
    }

    /**
     * Crea el Procedimiento de Control Documental
     */
    public function crearControlDocumental(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $anio = (int)date('Y');

        // Verificar si ya existe
        $existente = $this->db->table('tbl_documentos_sst')
            ->where('id_cliente', $idCliente)
            ->where('tipo_documento', 'procedimiento_control_documental')
            ->where('anio', $anio)
            ->get()
            ->getRowArray();

        if ($existente) {
            // Ya existe, redirigir al editor
            return redirect()->to(base_url('documentos/generar/procedimiento_control_documental/' . $idCliente));
        }

        // Generar código del documento
        $codigo = $this->generarCodigoDocumento($idCliente, 'procedimiento_control_documental');

        // Crear documento con secciones vacías (desde servicio)
        $tipoDoc = $this->configService->obtenerTipoDocumento('procedimiento_control_documental');
        $secciones = [];
        foreach ($tipoDoc['secciones'] as $sec) {
            $secciones[] = [
                'numero' => $sec['numero'],
                'nombre' => $sec['nombre'],
                'key' => $sec['key'],
                'contenido' => '',
                'estado' => 'pendiente'
            ];
        }

        $contenido = [
            'titulo' => $tipoDoc['nombre'],
            'secciones' => $secciones
        ];

        // Insertar documento
        $this->db->table('tbl_documentos_sst')->insert([
            'id_cliente' => $idCliente,
            'tipo_documento' => 'procedimiento_control_documental',
            'titulo' => $tipoDoc['nombre'],
            'codigo' => $codigo,
            'anio' => $anio,
            'contenido' => json_encode($contenido),
            'version' => 1,
            'estado' => 'borrador',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $idDocumento = $this->db->insertID();

        // Crear versión inicial
        $this->db->table('tbl_doc_versiones_sst')->insert([
            'id_documento' => $idDocumento,
            'id_cliente' => $idCliente,
            'codigo' => $codigo,
            'titulo' => $tipoDoc['nombre'],
            'anio' => $anio,
            'version' => 1,
            'version_texto' => '1.0',
            'tipo_cambio' => 'mayor',
            'descripcion_cambio' => 'Elaboración inicial del documento',
            'contenido_snapshot' => json_encode($contenido),
            'estado' => 'vigente',
            'fecha_autorizacion' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Redirigir al editor de secciones
        return redirect()->to(base_url('documentos/generar/procedimiento_control_documental/' . $idCliente))
            ->with('success', 'Procedimiento de Control Documental creado. Ahora puede editar las secciones.');
    }
}
