<?php

namespace App\Models;

use CodeIgniter\Model;

class HallazgoEppModel extends Model
{
    protected $table = 'tbl_hallazgo_epp';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_inspeccion',
        'tipo_epp', 'trabajador_area', 'descripcion',
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
