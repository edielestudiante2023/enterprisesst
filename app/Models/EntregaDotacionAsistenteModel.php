<?php

namespace App\Models;

use CodeIgniter\Model;

class EntregaDotacionAsistenteModel extends Model
{
    protected $table = 'tbl_entrega_dotacion_asistente';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_entrega_dotacion',
        'nombre_completo', 'tipo_documento', 'numero_documento',
        'cargo', 'area_dependencia', 'email', 'celular',
        'token_firma', 'token_expiracion', 'firma_path', 'firmado_at',
        'ruta_pdf',
        'orden', 'created_at',
    ];
    protected $useTimestamps = false;

    public function getByEntrega(int $idEntrega): array
    {
        return $this->where('id_entrega_dotacion', $idEntrega)
            ->orderBy('orden', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    public function getByToken(string $token): ?array
    {
        return $this->where('token_firma', $token)->first();
    }

    public function deleteByEntrega(int $idEntrega): bool
    {
        return $this->where('id_entrega_dotacion', $idEntrega)->delete();
    }
}
