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
        'estado', 'creado_por', 'fecha_aprobacion', 'aprobado_por',
        'fecha_vigencia', 'fecha_revision', 'tags'
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
        if (empty($datos['codigo']) && !empty($datos['codigo_tipo']) && !empty($datos['codigo_tema'])) {
            $datos['codigo'] = $this->generarCodigo(
                $datos['id_cliente'],
                $datos['codigo_tipo'],
                $datos['codigo_tema']
            );
            unset($datos['codigo_tipo'], $datos['codigo_tema']);
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
        return $this->where('id_cliente', $idCliente)
                    ->groupStart()
                        ->like('codigo', $termino)
                        ->orLike('nombre', $termino)
                        ->orLike('descripcion', $termino)
                        ->orLike('tags', $termino)
                    ->groupEnd()
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
                    ->where('fecha_revision <=', $fecha)
                    ->where('fecha_revision >=', date('Y-m-d'))
                    ->where('estado', 'aprobado')
                    ->orderBy('fecha_revision', 'ASC')
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
}
