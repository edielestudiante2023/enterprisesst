<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\Traits\TenantScopedModel;

class EppClienteModel extends Model
{
    use TenantScopedModel;

    protected $table = 'tbl_epp_cliente';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'id_cliente',
        'id_epp',
        'orden',
        'elemento',
        'norma',
        'mantenimiento',
        'frecuencia_cambio',
        'motivos_cambio',
        'momentos_uso',
        'observacion_cliente',
        'sincronizado_maestro',
        'fecha_ultima_sync',
        'activo',
    ];

    /**
     * Matriz completa del cliente con datos del maestro (foto + categoria).
     * Devuelve array agrupado por nombre de categoria.
     */
    public function matrizCliente(int $idCliente, ?string $tipo = null): array
    {
        $builder = $this->db->table("{$this->table} ec")
            ->select('ec.*, em.foto_path, cat.id_categoria, cat.nombre AS categoria_nombre, cat.tipo AS categoria_tipo, cat.orden AS categoria_orden')
            ->join('tbl_epp_maestro em', 'em.id_epp = ec.id_epp', 'left')
            ->join('tbl_epp_categoria cat', 'cat.id_categoria = em.id_categoria', 'left')
            ->where('ec.id_cliente', $idCliente)
            ->where('ec.activo', 1)
            ->orderBy('cat.orden', 'ASC')
            ->orderBy('ec.orden', 'ASC')
            ->orderBy('ec.elemento', 'ASC');
        if ($tipo) {
            $builder->where('cat.tipo', $tipo);
        }
        return $builder->get()->getResultArray();
    }

    public function existeAsignacion(int $idCliente, int $idEpp): bool
    {
        return (bool)$this->where('id_cliente', $idCliente)
            ->where('id_epp', $idEpp)
            ->first();
    }
}
