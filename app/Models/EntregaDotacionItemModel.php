<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Items GLOBALES de una entrega de dotacion (FK a tbl_entrega_dotacion).
 * Todos los operarios reciben los mismos items; la talla es por asistente
 * y se guarda en tbl_entrega_dotacion_asistente_talla.
 */
class EntregaDotacionItemModel extends Model
{
    protected $table = 'tbl_entrega_dotacion_item';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_entrega_dotacion',
        'descripcion', 'cantidad', 'marca',
        'orden', 'created_at',
    ];
    protected $useTimestamps = false;

    public function getByEntrega(int $idEntrega): array
    {
        return $this->where('id_entrega_dotacion', $idEntrega)
            ->orderBy('orden', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    public function deleteByEntrega(int $idEntrega): bool
    {
        return $this->where('id_entrega_dotacion', $idEntrega)->delete();
    }
}
