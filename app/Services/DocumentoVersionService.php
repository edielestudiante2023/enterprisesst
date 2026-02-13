<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use Config\Database;

/**
 * DocumentoVersionService
 *
 * Servicio CENTRALIZADO para gestionar versiones de TODOS los documentos SST.
 * Este servicio estandariza el proceso de versionamiento para garantizar
 * consistencia en todos los tipos de documentos.
 *
 * REGLAS DE VERSIONAMIENTO:
 * - Primera aprobacion: siempre version 1.0
 * - Cambio MAYOR: incrementa entero (1.0 -> 2.0 -> 3.0)
 * - Cambio MENOR: incrementa decimal (1.0 -> 1.1 -> 1.2)
 * - Descripcion del cambio: SIEMPRE obligatoria
 * - Snapshot del contenido: SIEMPRE se guarda
 *
 * Uso:
 *   $versionService = new DocumentoVersionService();
 *   $result = $versionService->iniciarNuevaVersion($idDoc, 'menor', 'Ajuste de datos');
 *   $result = $versionService->aprobarVersion($idDoc, $usuarioId, $usuarioNombre);
 */
class DocumentoVersionService
{
    protected BaseConnection $db;

    /** @var string Tabla principal de documentos */
    protected string $tablaDocumentos = 'tbl_documentos_sst';

    /** @var string Tabla de historial de versiones */
    protected string $tablaVersiones = 'tbl_doc_versiones_sst';

    /** @var array Tipos de cambio validos */
    protected array $tiposCambioValidos = ['mayor', 'menor'];

    /** @var array Estados validos de documentos */
    protected array $estadosDocumento = ['borrador', 'aprobado', 'pendiente_firma', 'firmado', 'generado', 'obsoleto'];

    /** @var array Estados validos de versiones */
    protected array $estadosVersion = ['vigente', 'obsoleto'];

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * Inicia el proceso de nueva version
     * Cambia el documento a estado 'borrador' para permitir edicion
     *
     * @param int $idDocumento ID del documento
     * @param string $tipoCambio 'mayor' o 'menor'
     * @param string $descripcion Descripcion del cambio (obligatoria)
     * @param array $datosAdicionales Datos extra para documentos especificos
     * @return array ['success' => bool, 'message' => string, 'data' => array]
     */
    public function iniciarNuevaVersion(
        int $idDocumento,
        string $tipoCambio,
        string $descripcion,
        array $datosAdicionales = []
    ): array {
        // Validar tipo de cambio
        if (!in_array($tipoCambio, $this->tiposCambioValidos)) {
            return [
                'success' => false,
                'message' => 'Tipo de cambio invalido. Use "mayor" o "menor"',
                'data' => null
            ];
        }

        // Validar descripcion
        if (empty(trim($descripcion))) {
            return [
                'success' => false,
                'message' => 'La descripcion del cambio es obligatoria',
                'data' => null
            ];
        }

        // Obtener documento
        $documento = $this->obtenerDocumento($idDocumento);
        if (!$documento) {
            return [
                'success' => false,
                'message' => 'Documento no encontrado',
                'data' => null
            ];
        }

        // Verificar que no este ya en borrador
        if ($documento['estado'] === 'borrador') {
            return [
                'success' => false,
                'message' => 'El documento ya esta en modo de edicion',
                'data' => null
            ];
        }

        // Calcular proxima version (predictivo, para mostrar al usuario)
        $proximaVersion = $this->calcularProximaVersion($idDocumento, $tipoCambio);

        try {
            // Cambiar estado a borrador y guardar motivo pendiente
            $this->db->table($this->tablaDocumentos)
                ->where('id_documento', $idDocumento)
                ->update([
                    'estado' => 'borrador',
                    'motivo_version' => $descripcion,
                    'tipo_cambio_pendiente' => $tipoCambio,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

            // Guardar datos adicionales si los hay
            if (!empty($datosAdicionales)) {
                $this->guardarDatosAdicionales($idDocumento, $datosAdicionales);
            }

            return [
                'success' => true,
                'message' => 'Nueva version iniciada. El documento esta en modo de edicion.',
                'data' => [
                    'id_documento' => $idDocumento,
                    'version_actual' => $documento['version'],
                    'version_actual_texto' => $this->obtenerVersionTextoActual($idDocumento),
                    'proxima_version' => $proximaVersion,
                    'tipo_cambio' => $tipoCambio,
                    'descripcion' => $descripcion,
                    'tipo_documento' => $documento['tipo_documento'],
                    'url_edicion' => $this->generarUrlEdicion($documento)
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error al iniciar nueva version: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al iniciar nueva version: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Aprueba el documento y crea una nueva version en el historial
     *
     * @param int $idDocumento ID del documento
     * @param int $usuarioId ID del usuario que aprueba
     * @param string $usuarioNombre Nombre del usuario
     * @param string|null $descripcionCambio Descripcion (opcional si ya se guardo en iniciarNuevaVersion)
     * @param string|null $tipoCambio Tipo de cambio (opcional si ya se guardo)
     * @return array ['success' => bool, 'message' => string, 'data' => array]
     */
    public function aprobarVersion(
        int $idDocumento,
        int $usuarioId,
        string $usuarioNombre,
        ?string $descripcionCambio = null,
        ?string $tipoCambio = null
    ): array {
        // Obtener documento
        $documento = $this->obtenerDocumento($idDocumento);
        if (!$documento) {
            return [
                'success' => false,
                'message' => 'Documento no encontrado',
                'data' => null
            ];
        }

        // Usar descripcion/tipo guardado si no se proporciona
        $descripcion = $descripcionCambio ?? $documento['motivo_version'] ?? '';
        $tipo = $tipoCambio ?? $documento['tipo_cambio_pendiente'] ?? 'menor';

        // Validar descripcion
        if (empty(trim($descripcion))) {
            return [
                'success' => false,
                'message' => 'La descripcion del cambio es obligatoria',
                'data' => null
            ];
        }

        // Contar versiones previas
        $versionesPrevias = $this->db->table($this->tablaVersiones)
            ->where('id_documento', $idDocumento)
            ->countAllResults();

        // Calcular nueva version
        $versionData = $this->calcularNuevaVersionFinal($idDocumento, $tipo, $versionesPrevias, (int)$documento['version']);

        try {
            $this->db->transStart();

            // 1. Marcar versiones anteriores como obsoletas
            $this->db->table($this->tablaVersiones)
                ->where('id_documento', $idDocumento)
                ->update(['estado' => 'obsoleto']);

            // 2. Crear nuevo registro de version con snapshot
            $datosVersion = [
                'id_documento' => $idDocumento,
                'id_cliente' => $documento['id_cliente'],
                'tipo_documento' => $documento['tipo_documento'],
                'codigo' => $documento['codigo'] ?? null,
                'titulo' => $documento['titulo'],
                'anio' => $documento['anio'],
                'version' => $versionData['version_entero'],
                'version_texto' => $versionData['version_texto'],
                'tipo_cambio' => $tipo,
                'descripcion_cambio' => $descripcion,
                'contenido_snapshot' => $documento['contenido'],
                'estado' => 'vigente',
                'autorizado_por' => $usuarioNombre,
                'autorizado_por_id' => $usuarioId,
                'fecha_autorizacion' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ];

            $this->db->table($this->tablaVersiones)->insert($datosVersion);
            $idVersion = $this->db->insertID();

            // 3. Actualizar documento principal
            $this->db->table($this->tablaDocumentos)
                ->where('id_documento', $idDocumento)
                ->update([
                    'version' => $versionData['version_entero'],
                    'estado' => 'aprobado',
                    'fecha_aprobacion' => date('Y-m-d H:i:s'),
                    'aprobado_por' => $usuarioId,
                    'motivo_version' => $descripcion,
                    'tipo_cambio_pendiente' => null,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Error en la transaccion de base de datos');
            }

            return [
                'success' => true,
                'message' => 'Documento aprobado correctamente. Version ' . $versionData['version_texto'],
                'data' => [
                    'id_documento' => $idDocumento,
                    'id_version' => $idVersion,
                    'version' => $versionData['version_entero'],
                    'version_texto' => $versionData['version_texto'],
                    'tipo_cambio' => $tipo,
                    'descripcion' => $descripcion,
                    'fecha_aprobacion' => date('Y-m-d H:i:s'),
                    'aprobado_por' => $usuarioNombre
                ]
            ];

        } catch (\Exception $e) {
            log_message('error', 'Error al aprobar version: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al aprobar documento: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Crea la version inicial de un documento (cuando se aprueba por primera vez)
     * Metodo simplificado para documentos nuevos
     *
     * @param int $idDocumento
     * @param int $usuarioId
     * @param string $usuarioNombre
     * @param string $descripcion Descripcion del documento inicial
     * @return array
     */
    public function crearVersionInicial(
        int $idDocumento,
        int $usuarioId,
        string $usuarioNombre,
        string $descripcion = 'Elaboracion inicial del documento'
    ): array {
        return $this->aprobarVersion($idDocumento, $usuarioId, $usuarioNombre, $descripcion, 'mayor');
    }

    /**
     * Obtiene el historial completo de versiones de un documento
     *
     * @param int $idDocumento
     * @param bool $soloVigente Si true, retorna solo la version vigente
     * @return array
     */
    public function obtenerHistorial(int $idDocumento, bool $soloVigente = false): array
    {
        $query = $this->db->table($this->tablaVersiones)
            ->where('id_documento', $idDocumento);

        if ($soloVigente) {
            $query->where('estado', 'vigente');
        }

        return $query->orderBy('fecha_autorizacion', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Obtiene una version especifica
     *
     * @param int $idVersion
     * @return array|null
     */
    public function obtenerVersion(int $idVersion): ?array
    {
        return $this->db->table($this->tablaVersiones)
            ->where('id_version', $idVersion)
            ->get()
            ->getRowArray();
    }

    /**
     * Obtiene la version vigente de un documento
     *
     * @param int $idDocumento
     * @return array|null
     */
    public function obtenerVersionVigente(int $idDocumento): ?array
    {
        return $this->db->table($this->tablaVersiones)
            ->where('id_documento', $idDocumento)
            ->where('estado', 'vigente')
            ->get()
            ->getRowArray();
    }

    /**
     * Restaura una version anterior
     * Crea una nueva version con el contenido de la version seleccionada
     *
     * @param int $idDocumento
     * @param int $idVersionRestaurar
     * @param int $usuarioId
     * @param string $usuarioNombre
     * @return array
     */
    public function restaurarVersion(
        int $idDocumento,
        int $idVersionRestaurar,
        int $usuarioId,
        string $usuarioNombre
    ): array {
        // Obtener version a restaurar
        $versionAnterior = $this->obtenerVersion($idVersionRestaurar);
        if (!$versionAnterior || $versionAnterior['id_documento'] != $idDocumento) {
            return [
                'success' => false,
                'message' => 'Version no encontrada o no pertenece a este documento',
                'data' => null
            ];
        }

        // Obtener documento actual
        $documento = $this->obtenerDocumento($idDocumento);
        if (!$documento) {
            return [
                'success' => false,
                'message' => 'Documento no encontrado',
                'data' => null
            ];
        }

        try {
            $this->db->transStart();

            // 1. Actualizar contenido del documento con el snapshot
            $this->db->table($this->tablaDocumentos)
                ->where('id_documento', $idDocumento)
                ->update([
                    'contenido' => $versionAnterior['contenido_snapshot'],
                    'estado' => 'borrador',
                    'motivo_version' => 'Restauracion a version ' . $versionAnterior['version_texto'],
                    'tipo_cambio_pendiente' => 'mayor',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Error en la transaccion');
            }

            return [
                'success' => true,
                'message' => 'Contenido restaurado a version ' . $versionAnterior['version_texto'] . '. Apruebe para crear la nueva version.',
                'data' => [
                    'id_documento' => $idDocumento,
                    'version_restaurada' => $versionAnterior['version_texto'],
                    'estado' => 'borrador'
                ]
            ];

        } catch (\Exception $e) {
            log_message('error', 'Error al restaurar version: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al restaurar version: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Compara dos versiones y retorna las diferencias
     *
     * @param int $idVersion1
     * @param int $idVersion2
     * @return array
     */
    public function compararVersiones(int $idVersion1, int $idVersion2): array
    {
        $version1 = $this->obtenerVersion($idVersion1);
        $version2 = $this->obtenerVersion($idVersion2);

        if (!$version1 || !$version2) {
            return [
                'success' => false,
                'message' => 'Una o ambas versiones no existen',
                'data' => null
            ];
        }

        $contenido1 = json_decode($version1['contenido_snapshot'], true) ?? [];
        $contenido2 = json_decode($version2['contenido_snapshot'], true) ?? [];

        $diferencias = [];
        $todasLasKeys = array_unique(array_merge(array_keys($contenido1), array_keys($contenido2)));

        foreach ($todasLasKeys as $key) {
            $valor1 = $contenido1[$key] ?? null;
            $valor2 = $contenido2[$key] ?? null;

            if ($valor1 !== $valor2) {
                $diferencias[$key] = [
                    'version_' . $version1['version_texto'] => $valor1,
                    'version_' . $version2['version_texto'] => $valor2
                ];
            }
        }

        return [
            'success' => true,
            'message' => count($diferencias) . ' diferencia(s) encontrada(s)',
            'data' => [
                'version1' => [
                    'version_texto' => $version1['version_texto'],
                    'fecha' => $version1['fecha_autorizacion']
                ],
                'version2' => [
                    'version_texto' => $version2['version_texto'],
                    'fecha' => $version2['fecha_autorizacion']
                ],
                'diferencias' => $diferencias
            ]
        ];
    }

    /**
     * Descarga el PDF de una version especifica (si existe)
     *
     * @param int $idVersion
     * @return array|null Ruta del archivo o null
     */
    public function obtenerArchivoPDF(int $idVersion): ?string
    {
        $version = $this->obtenerVersion($idVersion);
        return $version['archivo_pdf'] ?? null;
    }

    /**
     * Cancela la edicion de una nueva version y restaura el estado anterior
     *
     * @param int $idDocumento
     * @return array
     */
    public function cancelarNuevaVersion(int $idDocumento): array
    {
        $documento = $this->obtenerDocumento($idDocumento);
        if (!$documento) {
            return [
                'success' => false,
                'message' => 'Documento no encontrado',
                'data' => null
            ];
        }

        if ($documento['estado'] !== 'borrador') {
            return [
                'success' => false,
                'message' => 'El documento no esta en modo de edicion',
                'data' => null
            ];
        }

        // Obtener la ultima version aprobada para restaurar el contenido
        $ultimaVersion = $this->obtenerVersionVigente($idDocumento);

        try {
            $updateData = [
                'estado' => 'aprobado',
                'motivo_version' => null,
                'tipo_cambio_pendiente' => null,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Si hay version vigente, restaurar su contenido
            if ($ultimaVersion && !empty($ultimaVersion['contenido_snapshot'])) {
                $updateData['contenido'] = $ultimaVersion['contenido_snapshot'];
            }

            $this->db->table($this->tablaDocumentos)
                ->where('id_documento', $idDocumento)
                ->update($updateData);

            return [
                'success' => true,
                'message' => 'Edicion cancelada. El documento ha sido restaurado.',
                'data' => [
                    'id_documento' => $idDocumento,
                    'estado' => 'aprobado'
                ]
            ];

        } catch (\Exception $e) {
            log_message('error', 'Error al cancelar nueva version: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al cancelar: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    // ========================================================================
    // METODOS PRIVADOS
    // ========================================================================

    /**
     * Obtiene un documento por ID
     */
    protected function obtenerDocumento(int $idDocumento): ?array
    {
        return $this->db->table($this->tablaDocumentos)
            ->where('id_documento', $idDocumento)
            ->get()
            ->getRowArray();
    }

    /**
     * Calcula la proxima version (predictivo, para mostrar al usuario)
     */
    protected function calcularProximaVersion(int $idDocumento, string $tipoCambio): string
    {
        $documento = $this->obtenerDocumento($idDocumento);
        $versionActual = (int)($documento['version'] ?? 1);

        if ($tipoCambio === 'mayor') {
            return ($versionActual + 1) . '.0';
        }

        // Cambio menor: buscar el ultimo decimal
        $sql = "SELECT MAX(CAST(SUBSTRING_INDEX(version_texto, '.', -1) AS UNSIGNED)) as max_decimal
                FROM {$this->tablaVersiones}
                WHERE id_documento = ? AND version = ?";

        $resultado = $this->db->query($sql, [$idDocumento, $versionActual])->getRow();
        $decimal = ($resultado && $resultado->max_decimal !== null)
            ? (int)$resultado->max_decimal + 1
            : 1;

        return $versionActual . '.' . $decimal;
    }

    /**
     * Calcula la nueva version final (para guardar en BD)
     */
    protected function calcularNuevaVersionFinal(
        int $idDocumento,
        string $tipoCambio,
        int $versionesPrevias,
        int $versionActual
    ): array {
        // Primera aprobacion: siempre 1.0
        if ($versionesPrevias === 0) {
            return [
                'version_entero' => 1,
                'version_texto' => '1.0'
            ];
        }

        // Cambio mayor
        if ($tipoCambio === 'mayor') {
            $ultimaVersionMayor = $this->db->table($this->tablaVersiones)
                ->selectMax('version')
                ->where('id_documento', $idDocumento)
                ->get()
                ->getRow();

            $nuevaVersion = ($ultimaVersionMayor && $ultimaVersionMayor->version)
                ? (int)$ultimaVersionMayor->version + 1
                : $versionActual + 1;

            return [
                'version_entero' => $nuevaVersion,
                'version_texto' => $nuevaVersion . '.0'
            ];
        }

        // Cambio menor
        $sql = "SELECT MAX(CAST(SUBSTRING_INDEX(version_texto, '.', -1) AS UNSIGNED)) as max_decimal
                FROM {$this->tablaVersiones}
                WHERE id_documento = ? AND version = ?";

        $resultado = $this->db->query($sql, [$idDocumento, $versionActual])->getRow();
        $decimal = ($resultado && $resultado->max_decimal !== null)
            ? (int)$resultado->max_decimal + 1
            : 1;

        return [
            'version_entero' => $versionActual,
            'version_texto' => $versionActual . '.' . $decimal
        ];
    }

    /**
     * Obtiene el version_texto actual de un documento
     */
    protected function obtenerVersionTextoActual(int $idDocumento): string
    {
        $versionVigente = $this->obtenerVersionVigente($idDocumento);
        return $versionVigente['version_texto'] ?? '1.0';
    }

    /**
     * Genera la URL de edicion segun el tipo de documento
     */
    protected function generarUrlEdicion(array $documento): string
    {
        $tipo = $documento['tipo_documento'] ?? '';
        $idCliente = $documento['id_cliente'];
        $anio = $documento['anio'];

        // Mapeo de tipos a rutas
        $rutas = [
            'programa_capacitacion' => "documentos/generar/programa_capacitacion/{$idCliente}?anio={$anio}",
            'procedimiento_control_documental' => "documentos/generar/procedimiento_control_documental/{$idCliente}?anio={$anio}",
            'programa_induccion_reinduccion' => "documentos/generar/programa_induccion_reinduccion/{$idCliente}?anio={$anio}",
            'programa_promocion_prevencion_salud' => "generador-ia/{$idCliente}/pyp-salud",
            'procedimiento_matriz_legal' => "documentos/generar/procedimiento_matriz_legal/{$idCliente}?anio={$anio}",
            'presupuesto_sst' => "presupuesto-sst/editar/{$idCliente}/{$anio}",
            'responsabilidades_rep_legal_sgsst' => "documentos-sst/{$idCliente}/asignacion-responsable-sst/{$anio}",
            'responsabilidades_responsable_sst' => "documentos-sst/{$idCliente}/responsabilidades-responsable-sst/{$anio}",
            'plan_objetivos_metas' => "generador-ia/{$idCliente}/objetivos-sgsst",
        ];

        $ruta = $rutas[$tipo] ?? null;

        // Fichas técnicas de indicadores: tipo = ficha_tecnica_ind_XX
        if (!$ruta && str_starts_with($tipo, 'ficha_tecnica_ind_')) {
            $idIndicador = str_replace('ficha_tecnica_ind_', '', $tipo);
            $ruta = "indicadores-sst/{$idCliente}/ficha-tecnica/{$idIndicador}?anio={$anio}";
        }

        return base_url($ruta ?? "documentos-sst/{$idCliente}");
    }

    /**
     * Guarda datos adicionales especificos del tipo de documento
     */
    protected function guardarDatosAdicionales(int $idDocumento, array $datos): void
    {
        // Los datos adicionales se guardan en el contenido JSON
        $documento = $this->obtenerDocumento($idDocumento);
        $contenido = json_decode($documento['contenido'] ?? '{}', true) ?: [];

        // Merge de datos adicionales
        foreach ($datos as $key => $value) {
            $contenido[$key] = $value;
        }

        $this->db->table($this->tablaDocumentos)
            ->where('id_documento', $idDocumento)
            ->update([
                'contenido' => json_encode($contenido, JSON_UNESCAPED_UNICODE)
            ]);
    }

    /**
     * Obtiene estadisticas de versiones de un cliente
     */
    public function obtenerEstadisticasCliente(int $idCliente): array
    {
        $totalDocumentos = $this->db->table($this->tablaDocumentos)
            ->where('id_cliente', $idCliente)
            ->countAllResults();

        $totalVersiones = $this->db->table($this->tablaVersiones)
            ->where('id_cliente', $idCliente)
            ->countAllResults();

        $versionesVigentes = $this->db->table($this->tablaVersiones)
            ->where('id_cliente', $idCliente)
            ->where('estado', 'vigente')
            ->countAllResults();

        $documentosEnBorrador = $this->db->table($this->tablaDocumentos)
            ->where('id_cliente', $idCliente)
            ->where('estado', 'borrador')
            ->countAllResults();

        return [
            'total_documentos' => $totalDocumentos,
            'total_versiones' => $totalVersiones,
            'versiones_vigentes' => $versionesVigentes,
            'documentos_en_borrador' => $documentosEnBorrador,
            'promedio_versiones_por_documento' => $totalDocumentos > 0
                ? round($totalVersiones / $totalDocumentos, 2)
                : 0
        ];
    }
}
