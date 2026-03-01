<?php

namespace App\Models;

use CodeIgniter\Model;

class ElementoBotiquinModel extends Model
{
    protected $table = 'tbl_elemento_botiquin';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_inspeccion', 'clave', 'cantidad',
        'estado', 'fecha_vencimiento', 'orden',
    ];
    protected $useTimestamps = false;

    public function getByInspeccion(int $idInspeccion)
    {
        return $this->where('id_inspeccion', $idInspeccion)
            ->orderBy('orden', 'ASC')
            ->findAll();
    }

    public function deleteByInspeccion(int $idInspeccion)
    {
        return $this->where('id_inspeccion', $idInspeccion)->delete();
    }
}
