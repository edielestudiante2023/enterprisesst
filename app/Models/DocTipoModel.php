<?php
namespace App\Models;

use CodeIgniter\Model;

class DocTipoModel extends Model
{
    protected $table = 'tbl_doc_tipos';
    protected $primaryKey = 'id_tipo';
    protected $allowedFields = [
        'codigo', 'nombre', 'descripcion', 'estructura_secciones',
        'numero_secciones', 'tiene_secciones', 'requiere_firma_cliente', 'activo'
    ];

    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Obtiene tipos activos
     */
    public function getActivos(): array
    {
        return $this->where('activo', 1)
                    ->orderBy('nombre', 'ASC')
                    ->findAll();
    }

    /**
     * Obtiene tipo por código
     */
    public function getByCodigo(string $codigo): ?array
    {
        return $this->where('codigo', $codigo)->first();
    }

    /**
     * Obtiene estructura de secciones de un tipo
     */
    public function getEstructura(int $idTipo): array
    {
        $tipo = $this->find($idTipo);

        if (!$tipo || empty($tipo['estructura_secciones'])) {
            return [];
        }

        return json_decode($tipo['estructura_secciones'], true) ?? [];
    }

    /**
     * Obtiene tipos para selector
     */
    public function getParaSelector(): array
    {
        return $this->select('id_tipo, codigo, nombre, numero_secciones')
                    ->where('activo', 1)
                    ->orderBy('nombre', 'ASC')
                    ->findAll();
    }
}
