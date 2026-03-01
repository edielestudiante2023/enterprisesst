<?php

namespace App\Models;

use CodeIgniter\Model;

class ItemSenalizacionModel extends Model
{
    protected $table = 'tbl_item_senalizacion';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_inspeccion', 'nombre_item', 'grupo',
        'estado_cumplimiento', 'foto', 'orden',
    ];
    protected $useTimestamps = false;

    public function getByInspeccion(int $idInspeccion)
    {
        return $this->where('id_inspeccion', $idInspeccion)
            ->orderBy('orden', 'ASC')
            ->findAll();
    }

    public function getByInspeccionGrouped(int $idInspeccion): array
    {
        $items = $this->getByInspeccion($idInspeccion);
        $grouped = [];
        foreach ($items as $item) {
            $grouped[$item['grupo']][] = $item;
        }
        return $grouped;
    }

    public function deleteByInspeccion(int $idInspeccion)
    {
        return $this->where('id_inspeccion', $idInspeccion)->delete();
    }
}
