<?php

namespace App\Models;

use CodeIgniter\Model;

class RegistroAsistenciaAsistenteModel extends Model
{
    protected $table = 'tbl_registro_asistencia_asistente';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id_asistencia', 'nombre', 'cedula', 'cargo', 'firma'];
    protected $useTimestamps = true;
    protected $updatedField = '';

    public function getByAsistencia(int $idAsistencia)
    {
        return $this->where('id_asistencia', $idAsistencia)->orderBy('id', 'ASC')->findAll();
    }
}
