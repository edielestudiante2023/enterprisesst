<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use Config\Database;

/**
 * DocumentoConfigService
 *
 * Servicio para obtener la configuración de tipos de documentos desde la BD.
 * Reemplaza la constante TIPOS_DOCUMENTO hardcodeada en el controlador.
 *
 * Uso:
 *   $configService = new DocumentoConfigService();
 *   $config = $configService->obtenerTipoDocumento('procedimiento_control_documental');
 */
class DocumentoConfigService
{
    protected BaseConnection $db;

    /** @var array Cache de configuraciones cargadas */
    protected array $cache = [];

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * Obtiene la configuración completa de un tipo de documento
     *
     * @param string $tipoDocumento Identificador del tipo (ej: 'procedimiento_control_documental')
     * @return array|null Configuración completa o null si no existe
     */
    public function obtenerTipoDocumento(string $tipoDocumento): ?array
    {
        // Verificar cache
        if (isset($this->cache[$tipoDocumento])) {
            return $this->cache[$tipoDocumento];
        }

        // Buscar en BD
        $tipo = $this->db->table('tbl_doc_tipo_configuracion')
            ->where('tipo_documento', $tipoDocumento)
            ->where('activo', 1)
            ->get()
            ->getRowArray();

        if (!$tipo) {
            // Fallback: buscar en constante legacy si existe
            return $this->obtenerConfigLegacy($tipoDocumento);
        }

        // Obtener secciones
        $secciones = $this->db->table('tbl_doc_secciones_config')
            ->where('id_tipo_config', $tipo['id_tipo_config'])
            ->where('activo', 1)
            ->orderBy('orden', 'ASC')
            ->get()
            ->getResultArray();

        // Obtener firmantes
        $firmantes = $this->db->table('tbl_doc_firmantes_config')
            ->where('id_tipo_config', $tipo['id_tipo_config'])
            ->where('activo', 1)
            ->orderBy('orden', 'ASC')
            ->get()
            ->getResultArray();

        // Construir configuración
        $config = [
            'id_tipo_config' => $tipo['id_tipo_config'],
            'nombre' => $tipo['nombre'],
            'descripcion' => $tipo['descripcion'],
            'estandar' => $tipo['estandar'],
            'flujo' => $tipo['flujo'],
            'categoria' => $tipo['categoria'],
            'icono' => $tipo['icono'],
            'secciones' => array_map(fn($s) => [
                'numero' => (int)$s['numero'],
                'nombre' => $s['nombre'],
                'key' => $s['seccion_key'],
                'prompt_ia' => $s['prompt_ia'],
                'tipo_contenido' => $s['tipo_contenido'],
                'tabla_dinamica' => $s['tabla_dinamica_tipo'],
                'es_obligatoria' => (bool)$s['es_obligatoria']
            ], $secciones),
            'firmantes' => array_map(fn($f) => $f['firmante_tipo'], $firmantes),
            'firmantes_config' => $firmantes
        ];

        // Guardar en cache
        $this->cache[$tipoDocumento] = $config;

        return $config;
    }

    /**
     * Obtiene solo los firmantes de un tipo de documento
     *
     * @param string $tipoDocumento
     * @return array Lista de firmantes ['representante_legal', 'responsable_sst', ...]
     */
    public function obtenerFirmantes(string $tipoDocumento): array
    {
        $config = $this->obtenerTipoDocumento($tipoDocumento);
        return $config['firmantes'] ?? [];
    }

    /**
     * Obtiene la configuración detallada de firmantes
     *
     * @param string $tipoDocumento
     * @return array Lista con detalles de cada firmante
     */
    public function obtenerFirmantesConfig(string $tipoDocumento): array
    {
        $config = $this->obtenerTipoDocumento($tipoDocumento);
        return $config['firmantes_config'] ?? [];
    }

    /**
     * Obtiene las secciones de un tipo de documento
     *
     * @param string $tipoDocumento
     * @return array Lista de secciones
     */
    public function obtenerSecciones(string $tipoDocumento): array
    {
        $config = $this->obtenerTipoDocumento($tipoDocumento);
        return $config['secciones'] ?? [];
    }

    /**
     * Obtiene el prompt de IA para una sección específica
     *
     * @param string $tipoDocumento
     * @param string $seccionKey
     * @return string|null Prompt o null si no existe
     */
    public function obtenerPromptSeccion(string $tipoDocumento, string $seccionKey): ?string
    {
        $secciones = $this->obtenerSecciones($tipoDocumento);

        foreach ($secciones as $seccion) {
            if ($seccion['key'] === $seccionKey) {
                return $seccion['prompt_ia'];
            }
        }

        return null;
    }

    /**
     * Obtiene todos los tipos de documento activos
     *
     * @param string|null $categoria Filtrar por categoría (opcional)
     * @return array Lista de tipos de documento
     */
    public function obtenerTodos(?string $categoria = null): array
    {
        $query = $this->db->table('tbl_doc_tipo_configuracion')
            ->where('activo', 1);

        if ($categoria) {
            $query->where('categoria', $categoria);
        }

        return $query->orderBy('orden', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Obtiene la configuración de una tabla dinámica
     *
     * @param string $tablaKey Identificador de la tabla (ej: 'tipos_documento')
     * @return array|null Configuración de la tabla
     */
    public function obtenerTablaDinamica(string $tablaKey): ?array
    {
        $tabla = $this->db->table('tbl_doc_tablas_dinamicas')
            ->where('tabla_key', $tablaKey)
            ->where('activo', 1)
            ->get()
            ->getRowArray();

        if (!$tabla) {
            return null;
        }

        // Decodificar columnas JSON
        $tabla['columnas'] = json_decode($tabla['columnas'], true) ?? [];

        return $tabla;
    }

    /**
     * Ejecuta la query de una tabla dinámica y retorna los datos
     *
     * @param string $tablaKey
     * @param int|null $idCliente ID del cliente (si la tabla lo requiere)
     * @return array Datos de la tabla
     */
    public function obtenerDatosTablaDinamica(string $tablaKey, ?int $idCliente = null): array
    {
        $config = $this->obtenerTablaDinamica($tablaKey);

        if (!$config) {
            return [];
        }

        $query = $config['query_base'];

        // Reemplazar placeholder de cliente si existe
        if ($config['filtro_cliente'] && $idCliente) {
            $query = str_replace(':id_cliente', (string)$idCliente, $query);
        }

        try {
            return $this->db->query($query)->getResultArray();
        } catch (\Exception $e) {
            log_message('error', "Error en tabla dinámica '$tablaKey': " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verifica si un tipo de documento existe en BD
     *
     * @param string $tipoDocumento
     * @return bool
     */
    public function existe(string $tipoDocumento): bool
    {
        return $this->obtenerTipoDocumento($tipoDocumento) !== null;
    }

    /**
     * Obtiene categorías disponibles
     *
     * @return array Lista de categorías únicas
     */
    public function obtenerCategorias(): array
    {
        return $this->db->table('tbl_doc_tipo_configuracion')
            ->select('categoria')
            ->where('activo', 1)
            ->where('categoria IS NOT NULL')
            ->groupBy('categoria')
            ->get()
            ->getResultArray();
    }

    /**
     * Fallback: Obtiene configuración desde constante legacy
     * Para compatibilidad durante la migración
     *
     * @param string $tipoDocumento
     * @return array|null
     */
    protected function obtenerConfigLegacy(string $tipoDocumento): ?array
    {
        // Constante legacy del controlador
        $tiposLegacy = [
            'programa_capacitacion' => [
                'nombre' => 'Programa de Capacitación en SST',
                'descripcion' => 'Define las actividades de capacitación',
                'flujo' => 'programa_con_pta',
                'estandar' => '3.1.1',
                'firmantes' => ['representante_legal', 'responsable_sst', 'delegado_sst'],
                'secciones' => [
                    ['numero' => 1, 'nombre' => 'Introduccion', 'key' => 'introduccion'],
                    ['numero' => 2, 'nombre' => 'Objetivo General', 'key' => 'objetivo_general'],
                    // ... secciones adicionales
                ]
            ],
            'procedimiento_control_documental' => [
                'nombre' => 'Procedimiento de Control Documental del SG-SST',
                'descripcion' => 'Establece las directrices para la elaboración, revisión, aprobación, distribución y conservación de documentos del SG-SST',
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
                    ['numero' => 7, 'nombre' => 'Estructura y Codificación', 'key' => 'codificacion'],
                    ['numero' => 8, 'nombre' => 'Elaboración de Documentos', 'key' => 'elaboracion'],
                    ['numero' => 9, 'nombre' => 'Revisión y Aprobación', 'key' => 'revision_aprobacion'],
                    ['numero' => 10, 'nombre' => 'Distribución y Acceso', 'key' => 'distribucion'],
                    ['numero' => 11, 'nombre' => 'Control de Cambios', 'key' => 'control_cambios'],
                    ['numero' => 12, 'nombre' => 'Conservación y Retención', 'key' => 'conservacion'],
                    ['numero' => 13, 'nombre' => 'Listado Maestro de Documentos', 'key' => 'listado_maestro'],
                    ['numero' => 14, 'nombre' => 'Disposición Final', 'key' => 'disposicion_final'],
                ]
            ],
            'programa_induccion_reinduccion' => [
                'nombre' => 'Programa de Inducción y Reinducción en SG-SST',
                'descripcion' => 'Establece el proceso de inducción y reinducción para todos los trabajadores',
                'flujo' => 'secciones_ia',
                'estandar' => '1.2.2',
                'firmantes' => ['representante_legal', 'responsable_sst'],
                'secciones' => [
                    ['numero' => 1, 'nombre' => 'Objetivo', 'key' => 'objetivo'],
                    ['numero' => 2, 'nombre' => 'Alcance', 'key' => 'alcance'],
                    ['numero' => 3, 'nombre' => 'Requisitos Generales', 'key' => 'requisitos_generales'],
                    ['numero' => 4, 'nombre' => 'Contenido: Esquema General', 'key' => 'contenido_esquema'],
                    ['numero' => 5, 'nombre' => 'Etapa 1: Introducción a la Empresa', 'key' => 'etapa_introduccion'],
                    ['numero' => 6, 'nombre' => 'Etapa 2: Seguridad y Salud en el Trabajo', 'key' => 'etapa_sst'],
                    ['numero' => 7, 'nombre' => 'Etapa 3: Relaciones Laborales', 'key' => 'etapa_relaciones'],
                    ['numero' => 8, 'nombre' => 'Etapa 4: Recorrido de Instalaciones', 'key' => 'etapa_recorrido'],
                    ['numero' => 9, 'nombre' => 'Etapa 5: Entrenamiento al Cargo', 'key' => 'etapa_entrenamiento'],
                    ['numero' => 10, 'nombre' => 'Entrega de Memorias', 'key' => 'entrega_memorias'],
                    ['numero' => 11, 'nombre' => 'Evaluación y Control', 'key' => 'evaluacion_control'],
                    ['numero' => 12, 'nombre' => 'Indicadores del Programa', 'key' => 'indicadores'],
                    ['numero' => 13, 'nombre' => 'Cronograma de Actividades', 'key' => 'cronograma'],
                ]
            ]
        ];

        return $tiposLegacy[$tipoDocumento] ?? null;
    }

    /**
     * Limpia el cache de configuraciones
     */
    public function limpiarCache(): void
    {
        $this->cache = [];
    }

    /**
     * SOLUCIÓN ARQUITECTÓNICA: Crea contenido inicial dinámico desde BD
     *
     * Este método elimina la necesidad de hardcodear el contenido inicial en
     * los controladores. Lee las secciones configuradas en BD y genera una
     * estructura compatible con el sistema de documentos.
     *
     * PATRÓN DE USO:
     *   $configService = new DocumentoConfigService();
     *   $contenidoInicial = $configService->crearContenidoInicial('plan_objetivos_metas');
     *
     * ANTES (hardcodeado en controlador):
     *   $contenido = ['secciones' => [
     *       'objetivo' => 'texto...',
     *       'alcance' => 'texto...',
     *   ]];
     *
     * DESPUÉS (dinámico desde BD):
     *   $contenido = $configService->crearContenidoInicial('plan_objetivos_metas');
     *
     * @param string $tipoDocumento Identificador del tipo de documento
     * @param array $valoresIniciales Opcional: valores por defecto para secciones específicas
     *                                 Formato: ['key_seccion' => 'valor inicial']
     * @return array Estructura de contenido compatible con documentos SST:
     *               ['secciones' => ['key1' => 'valor1', 'key2' => 'valor2', ...]]
     */
    public function crearContenidoInicial(string $tipoDocumento, array $valoresIniciales = []): array
    {
        $secciones = $this->obtenerSecciones($tipoDocumento);

        if (empty($secciones)) {
            log_message('warning', "crearContenidoInicial: No hay secciones configuradas para '$tipoDocumento'");
            return ['secciones' => []];
        }

        $contenido = ['secciones' => []];

        foreach ($secciones as $seccion) {
            $key = $seccion['key'];
            $nombre = $seccion['nombre'];

            // Prioridad: 1) Valor inicial provisto, 2) String vacío
            if (isset($valoresIniciales[$key])) {
                $contenido['secciones'][$key] = $valoresIniciales[$key];
            } else {
                // Valor por defecto vacío - la IA lo llenará
                $contenido['secciones'][$key] = '';
            }

            // Agregar metadatos útiles para la vista
            $contenido['_meta'][$key] = [
                'nombre' => $nombre,
                'numero' => $seccion['numero'],
                'prompt_ia' => $seccion['prompt_ia'] ?? null,
                'tipo_contenido' => $seccion['tipo_contenido'] ?? 'texto',
                'es_obligatoria' => $seccion['es_obligatoria'] ?? true
            ];
        }

        return $contenido;
    }

    /**
     * Genera contenido inicial con datos de contexto del cliente
     *
     * Versión extendida de crearContenidoInicial que puede inyectar
     * datos dinámicos del cliente (objetivos, indicadores, etc.)
     *
     * @param string $tipoDocumento Identificador del tipo
     * @param int $idCliente ID del cliente
     * @param array $contextoAdicional Datos adicionales para incluir
     * @return array Contenido con secciones pobladas
     */
    public function crearContenidoConContexto(string $tipoDocumento, int $idCliente, array $contextoAdicional = []): array
    {
        $contenido = $this->crearContenidoInicial($tipoDocumento);
        $secciones = $this->obtenerSecciones($tipoDocumento);

        // Procesar tablas dinámicas si están configuradas
        foreach ($secciones as $seccion) {
            $key = $seccion['key'];
            $tablaDinamica = $seccion['tabla_dinamica'] ?? null;

            if ($tablaDinamica) {
                $datos = $this->obtenerDatosTablaDinamica($tablaDinamica, $idCliente);
                if (!empty($datos)) {
                    $contenido['_tablas'][$key] = $datos;
                }
            }
        }

        // Agregar contexto adicional
        if (!empty($contextoAdicional)) {
            $contenido['_contexto'] = $contextoAdicional;
        }

        return $contenido;
    }

    /**
     * Obtiene el mapeo de keys de secciones para un tipo de documento
     *
     * Útil para normalizarSecciones() y otros procesos de matching
     *
     * @param string $tipoDocumento
     * @return array ['key1' => 'Nombre Sección 1', 'key2' => 'Nombre Sección 2', ...]
     */
    public function obtenerMapeoSecciones(string $tipoDocumento): array
    {
        $secciones = $this->obtenerSecciones($tipoDocumento);
        $mapeo = [];

        foreach ($secciones as $seccion) {
            $mapeo[$seccion['key']] = $seccion['nombre'];
        }

        return $mapeo;
    }
}
