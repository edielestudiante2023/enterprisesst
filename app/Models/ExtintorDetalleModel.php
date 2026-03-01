<?php

namespace App\Models;

use CodeIgniter\Model;

class ExtintorDetalleModel extends Model
{
    protected $table = 'tbl_extintor_detalle';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_inspeccion',
        'pintura_cilindro', 'golpes_extintor', 'autoadhesivo',
        'manija_transporte', 'palanca_accionamiento',
        'presion', 'manometro', 'boquilla', 'manguera',
        'ring_seguridad', 'senalizacion', 'soporte',
        'fecha_vencimiento', 'foto', 'observaciones', 'orden',
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
