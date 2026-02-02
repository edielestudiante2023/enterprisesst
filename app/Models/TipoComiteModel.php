<?php

namespace App\Models;

use CodeIgniter\Model;

class TipoComiteModel extends Model
{
    protected $table = 'tbl_tipos_comite';
    protected $primaryKey = 'id_tipo';
    protected $returnType = 'array';

    protected $allowedFields = [
        'codigo',
        'nombre',
        'descripcion',
        'periodicidad_dias',
        'dia_limite_mes',
        'requiere_paridad',
        'requiere_quorum',
        'quorum_minimo_porcentaje',
        'vigencia_periodo_meses',
        'activo'
    ];

    protected $useTimestamps = false;

    public function getActivos(): array
    {
        return $this->where('activo', 1)->findAll();
    }

    public function getByCodigo(string $codigo): ?array
    {
        return $this->where('codigo', $codigo)->first();
    }
}
