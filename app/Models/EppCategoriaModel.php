<?php

namespace App\Models;

use CodeIgniter\Model;

class EppCategoriaModel extends Model
{
    protected $table = 'tbl_epp_categoria';
    protected $primaryKey = 'id_categoria';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = ['nombre', 'tipo', 'orden', 'activo'];

    public function activas(): array
    {
        return $this->where('activo', 1)
            ->orderBy('orden', 'ASC')
            ->orderBy('nombre', 'ASC')
            ->findAll();
    }
}
