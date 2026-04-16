<?php

namespace App\Models;

use CodeIgniter\Model;

class EppMaestroModel extends Model
{
    protected $table = 'tbl_epp_maestro';
    protected $primaryKey = 'id_epp';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'id_categoria',
        'elemento',
        'norma',
        'mantenimiento',
        'frecuencia_cambio',
        'motivos_cambio',
        'momentos_uso',
        'foto_path',
        'ia_generado',
        'activo',
    ];

    /**
     * Lista activa con join a categoria, filtros opcionales.
     */
    public function listar(array $filtros = []): array
    {
        $builder = $this->db->table("{$this->table} em")
            ->select('em.*, cat.nombre AS categoria_nombre, cat.tipo AS categoria_tipo')
            ->join('tbl_epp_categoria cat', 'cat.id_categoria = em.id_categoria', 'left')
            ->where('em.activo', 1)
            ->orderBy('cat.orden', 'ASC')
            ->orderBy('em.elemento', 'ASC');

        if (!empty($filtros['id_categoria'])) {
            $builder->where('em.id_categoria', (int)$filtros['id_categoria']);
        }
        if (!empty($filtros['tipo'])) {
            $builder->where('cat.tipo', $filtros['tipo']);
        }
        if (isset($filtros['ia_generado']) && $filtros['ia_generado'] !== '') {
            $builder->where('em.ia_generado', (int)$filtros['ia_generado']);
        }
        if (!empty($filtros['q'])) {
            $q = '%' . $filtros['q'] . '%';
            $builder->groupStart()
                ->like('em.elemento', $filtros['q'])
                ->orLike('em.norma', $filtros['q'])
                ->groupEnd();
        }

        return $builder->get()->getResultArray();
    }

    public function conCategoria(int $idEpp): ?array
    {
        $row = $this->db->table("{$this->table} em")
            ->select('em.*, cat.nombre AS categoria_nombre, cat.tipo AS categoria_tipo')
            ->join('tbl_epp_categoria cat', 'cat.id_categoria = em.id_categoria', 'left')
            ->where('em.id_epp', $idEpp)
            ->get()->getRowArray();
        return $row ?: null;
    }
}
