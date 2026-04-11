<?php

namespace App\Models;

use CodeIgniter\Model;

class PausaActivaRegistroModel extends Model
{
    protected $table = 'tbl_pausa_activa_registros';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_pausa', 'tipo_pausa', 'imagen', 'orden', 'created_at',
    ];
    protected $useTimestamps = false;

    public function getByPausa(int $idPausa): array
    {
        return $this->where('id_pausa', $idPausa)
            ->orderBy('orden', 'ASC')
            ->findAll();
    }

    public function deleteByPausa(int $idPausa): bool
    {
        return $this->where('id_pausa', $idPausa)->delete();
    }
}
