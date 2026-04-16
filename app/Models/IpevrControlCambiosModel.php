<?php

namespace App\Models;

use CodeIgniter\Model;

class IpevrControlCambiosModel extends Model
{
    protected $table = 'tbl_ipevr_control_cambios';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'id_matriz',
        'version',
        'descripcion',
        'fecha',
        'usuario',
        'id_usuario',
    ];

    public function porMatriz(int $idMatriz): array
    {
        return $this->where('id_matriz', $idMatriz)
            ->orderBy('fecha', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll();
    }
}
