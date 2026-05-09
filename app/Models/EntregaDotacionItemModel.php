<?php

namespace App\Models;

use CodeIgniter\Model;

class EntregaDotacionItemModel extends Model
{
    protected $table = 'tbl_entrega_dotacion_item';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_entrega_dotacion_asistente',
        'descripcion', 'cantidad', 'talla', 'marca',
        'orden', 'created_at',
    ];
    protected $useTimestamps = false;

    public function getByAsistente(int $idAsistente): array
    {
        return $this->where('id_entrega_dotacion_asistente', $idAsistente)
            ->orderBy('orden', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    public function deleteByAsistente(int $idAsistente): bool
    {
        return $this->where('id_entrega_dotacion_asistente', $idAsistente)->delete();
    }
}
