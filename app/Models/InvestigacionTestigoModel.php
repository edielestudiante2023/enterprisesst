<?php

namespace App\Models;

use CodeIgniter\Model;

class InvestigacionTestigoModel extends Model
{
    protected $table = 'tbl_investigacion_testigos';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_investigacion', 'nombre', 'cargo', 'declaracion',
    ];
    protected $useTimestamps = false;

    public function getByInvestigacion(int $idInvestigacion): array
    {
        return $this->where('id_investigacion', $idInvestigacion)->findAll();
    }

    public function deleteByInvestigacion(int $idInvestigacion): bool
    {
        return $this->where('id_investigacion', $idInvestigacion)->delete();
    }
}
