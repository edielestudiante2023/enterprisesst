<?php

namespace App\Models;

use CodeIgniter\Model;

class HallazgoLocativoModel extends Model
{
    protected $table = 'tbl_hallazgo_locativo';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_inspeccion', 'descripcion',
        'imagen', 'imagen_correccion',
        'fecha_hallazgo', 'fecha_correccion',
        'estado', 'observaciones', 'orden',
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
