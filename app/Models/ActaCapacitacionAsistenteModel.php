<?php

namespace App\Models;

use CodeIgniter\Model;

class ActaCapacitacionAsistenteModel extends Model
{
    protected $table = 'tbl_acta_capacitacion_asistente';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_acta_capacitacion',
        'nombre_completo', 'tipo_documento', 'numero_documento',
        'cargo', 'area_dependencia', 'email', 'celular',
        'token_firma', 'token_expiracion', 'firma_path', 'firmado_at',
        'orden', 'created_at',
    ];
    protected $useTimestamps = false;

    public function getByActa(int $idActa): array
    {
        return $this->where('id_acta_capacitacion', $idActa)
            ->orderBy('orden', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    public function getByToken(string $token): ?array
    {
        return $this->where('token_firma', $token)->first();
    }

    public function deleteByActa(int $idActa): bool
    {
        return $this->where('id_acta_capacitacion', $idActa)->delete();
    }
}
