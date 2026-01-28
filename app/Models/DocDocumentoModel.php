<?php
namespace App\Models;

use CodeIgniter\Model;

class DocDocumentoModel extends Model
{
    protected $table = 'tbl_doc_documentos';
    protected $primaryKey = 'id_documento';
    protected $allowedFields = [
        'id_cliente', 'id_carpeta', 'id_tipo', 'id_plantilla',
        'codigo', 'nombre', 'descripcion', 'version_actual',
        'estado', 'fecha_emision', 'fecha_aprobacion', 'fecha_proxima_revision',
        'elaboro_usuario_id', 'reviso_usuario_id', 'aprobo_contacto_id',
        'estandares_relacionados', 'tags', 'activo'
    ];

    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Obtiene documentos de un cliente
     */
    public function getByCliente(int $idCliente, ?string $estado = null): array
    {
        $builder = $this->where('id_cliente', $idCliente);

        if ($estado) {
            $builder->where('estado', $estado);
        }

        return $builder->orderBy('updated_at', 'DESC')->findAll();
    }

    /**
     * Obtiene documentos de una carpeta
     */
    public function getByCarpeta(int $idCarpeta): array
    {
        return $this->where('id_carpeta', $idCarpeta)
                    ->orderBy('codigo', 'ASC')
                    ->findAll();
    }

    /**
     * Obtiene documento con información completa
     */
    public function getCompleto(int $idDocumento): ?array
    {
        return $this->select('tbl_doc_documentos.*,
                             tbl_doc_tipos.nombre as tipo_nombre,
                             tbl_doc_tipos.estructura_secciones,
                             tbl_doc_carpetas.nombre as carpeta_nombre,
                             tbl_clientes.nombre_cliente')
                    ->join('tbl_doc_tipos', 'tbl_doc_tipos.id_tipo = tbl_doc_documentos.id_tipo', 'left')
                    ->join('tbl_doc_carpetas', 'tbl_doc_carpetas.id_carpeta = tbl_doc_documentos.id_carpeta', 'left')
                    ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_doc_documentos.id_cliente')
                    ->where('tbl_doc_documentos.id_documento', $idDocumento)
                    ->first();
    }

    /**
     * Genera código para nuevo documento usando SP
     */
    public function generarCodigo(int $idCliente, string $codigoTipo, string $codigoTema): string
    {
        $db = \Config\Database::connect();

        $db->query("CALL sp_generar_codigo_documento(?, ?, ?, @codigo)", [
            $idCliente,
            $codigoTipo,
            $codigoTema
        ]);

        $result = $db->query("SELECT @codigo as codigo")->getRowArray();

        return $result['codigo'] ?? '';
    }

    /**
     * Crea un nuevo documento
     */
    public function crearDocumento(array $datos): int
    {
        // Generar código si no viene
        if (empty($datos['codigo'])) {
            if (!empty($datos['codigo_tipo']) && !empty($datos['codigo_tema'])) {
                $datos['codigo'] = $this->generarCodigo(
                    $datos['id_cliente'],
                    $datos['codigo_tipo'],
                    $datos['codigo_tema']
                );
            } else {
                // Generar código automático basado en tipo e ID
                $tipoCode = 'DOC';
                if (!empty($datos['id_tipo'])) {
                    $tipo = (new DocTipoModel())->find($datos['id_tipo']);
                    $tipoCode = $tipo['codigo'] ?? 'DOC';
                }
                $datos['codigo'] = $this->generarCodigo(
                    $datos['id_cliente'],
                    $tipoCode,
                    'GEN'
                );
            }
            unset($datos['codigo_tipo'], $datos['codigo_tema']);
        }

        // Verificar que no exista ya con el mismo código para este cliente
        $existente = $this->where('id_cliente', $datos['id_cliente'])
                         ->where('codigo', $datos['codigo'])
                         ->first();

        if ($existente) {
            // Si ya existe, retornar el ID existente (reutilizar documento en borrador)
            if ($existente['estado'] === 'borrador') {
                return (int)$existente['id_documento'];
            }
            // Si no es borrador, agregar sufijo para hacerlo único
            $datos['codigo'] .= '-' . date('His');
        }

        // Valores por defecto
        $datos['version_actual'] = $datos['version_actual'] ?? '1.0';
        $datos['estado'] = $datos['estado'] ?? 'borrador';

        $this->insert($datos);

        return $this->getInsertID();
    }

    /**
     * Cambia estado del documento
     */
    public function cambiarEstado(int $idDocumento, string $estado): bool
    {
        $data = ['estado' => $estado];

        if ($estado === 'aprobado') {
            $data['fecha_aprobacion'] = date('Y-m-d H:i:s');
        }

        return $this->update($idDocumento, $data);
    }

    /**
     * Busca documentos
     */
    public function buscar(int $idCliente, string $termino): array
    {
        $terminoEscapado = $this->db->escapeLikeString($termino);
        $collate = 'COLLATE utf8mb4_general_ci';

        return $this->where('id_cliente', $idCliente)
                    ->where("(codigo {$collate} LIKE '%{$terminoEscapado}%' OR nombre {$collate} LIKE '%{$terminoEscapado}%' OR descripcion {$collate} LIKE '%{$terminoEscapado}%' OR tags {$collate} LIKE '%{$terminoEscapado}%')", null, false)
                    ->orderBy('updated_at', 'DESC')
                    ->findAll();
    }

    /**
     * Obtiene documentos por estado con estadísticas
     */
    public function getEstadisticas(int $idCliente): array
    {
        $result = $this->select('estado, COUNT(*) as cantidad')
                       ->where('id_cliente', $idCliente)
                       ->groupBy('estado')
                       ->findAll();

        $stats = [
            'borrador' => 0,
            'en_revision' => 0,
            'pendiente_firma' => 0,
            'aprobado' => 0,
            'obsoleto' => 0,
            'total' => 0
        ];

        foreach ($result as $row) {
            $stats[$row['estado']] = (int) $row['cantidad'];
            $stats['total'] += (int) $row['cantidad'];
        }

        return $stats;
    }

    /**
     * Obtiene documentos próximos a revisión
     */
    public function getProximosRevision(int $idCliente, int $dias = 30): array
    {
        $fecha = date('Y-m-d', strtotime("+{$dias} days"));

        return $this->where('id_cliente', $idCliente)
                    ->where('fecha_proxima_revision <=', $fecha)
                    ->where('fecha_proxima_revision >=', date('Y-m-d'))
                    ->where('estado', 'aprobado')
                    ->orderBy('fecha_proxima_revision', 'ASC')
                    ->findAll();
    }

    /**
     * Obtiene documentos recientes
     */
    public function getRecientes(int $idCliente, int $limite = 10): array
    {
        return $this->where('id_cliente', $idCliente)
                    ->orderBy('updated_at', 'DESC')
                    ->limit($limite)
                    ->findAll();
    }

    /**
     * Verifica si existe un documento con el mismo código
     */
    public function existeCodigo(int $idCliente, string $codigo, ?int $exceptoId = null): bool
    {
        $builder = $this->where('id_cliente', $idCliente)
                        ->where('codigo', $codigo);

        if ($exceptoId) {
            $builder->where('id_documento !=', $exceptoId);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * Calcula el estado IA de un documento basado en sus secciones
     * - pendiente: ninguna sección tiene contenido
     * - creado: alguna sección tiene contenido pero no todas aprobadas
     * - aprobado: todas las secciones están aprobadas
     */
    public function getEstadoIA(int $idDocumento): string
    {
        $db = \Config\Database::connect();

        $result = $db->table('tbl_doc_secciones')
            ->select('
                COUNT(*) as total,
                SUM(CASE WHEN contenido IS NOT NULL AND contenido != "" THEN 1 ELSE 0 END) as con_contenido,
                SUM(CASE WHEN aprobado = 1 THEN 1 ELSE 0 END) as aprobadas
            ')
            ->where('id_documento', $idDocumento)
            ->get()
            ->getRowArray();

        $total = (int)($result['total'] ?? 0);
        $conContenido = (int)($result['con_contenido'] ?? 0);
        $aprobadas = (int)($result['aprobadas'] ?? 0);

        // Si no hay secciones, está pendiente
        if ($total === 0) {
            return 'pendiente';
        }

        // Si todas las secciones están aprobadas
        if ($aprobadas === $total) {
            return 'aprobado';
        }

        // Si alguna sección tiene contenido
        if ($conContenido > 0) {
            return 'creado';
        }

        return 'pendiente';
    }

    /**
     * Obtiene documentos de una carpeta con su estado IA calculado
     */
    public function getByCarpetaConEstadoIA(int $idCarpeta): array
    {
        $documentos = $this->where('id_carpeta', $idCarpeta)
                          ->orderBy('codigo', 'ASC')
                          ->findAll();

        foreach ($documentos as &$doc) {
            $doc['estado_ia'] = $this->getEstadoIA($doc['id_documento']);
        }

        return $documentos;
    }

    /**
     * Obtiene documentos de un cliente con su estado IA calculado
     */
    public function getByClienteConEstadoIA(int $idCliente): array
    {
        $documentos = $this->where('id_cliente', $idCliente)
                          ->orderBy('updated_at', 'DESC')
                          ->findAll();

        foreach ($documentos as &$doc) {
            $doc['estado_ia'] = $this->getEstadoIA($doc['id_documento']);
        }

        return $documentos;
    }

    /**
     * Obtiene estadísticas de estado IA por carpeta
     */
    public function getEstadisticasIAPorCarpeta(int $idCarpeta): array
    {
        $documentos = $this->where('id_carpeta', $idCarpeta)->findAll();

        $stats = [
            'total' => count($documentos),
            'pendiente' => 0,
            'creado' => 0,
            'aprobado' => 0
        ];

        foreach ($documentos as $doc) {
            $estadoIA = $this->getEstadoIA($doc['id_documento']);
            $stats[$estadoIA]++;
        }

        return $stats;
    }
}
