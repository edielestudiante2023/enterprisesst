<?php

namespace App\Models;

use CodeIgniter\Model;

class InvestigacionMedidaModel extends Model
{
    protected $table = 'tbl_investigacion_medidas';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_investigacion', 'tipo_medida', 'descripcion', 'responsable',
        'fecha_cumplimiento', 'estado',
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
