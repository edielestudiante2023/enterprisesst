<?php

namespace App\Models;

use CodeIgniter\Model;

class InvestigacionEvidenciaModel extends Model
{
    protected $table = 'tbl_investigacion_evidencia';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_investigacion', 'descripcion', 'imagen', 'orden',
    ];
    protected $useTimestamps = false;

    public function getByInvestigacion(int $idInvestigacion): array
    {
        return $this->where('id_investigacion', $idInvestigacion)
            ->orderBy('orden', 'ASC')
            ->findAll();
    }

    public function deleteByInvestigacion(int $idInvestigacion): bool
    {
        return $this->where('id_investigacion', $idInvestigacion)->delete();
    }
}
