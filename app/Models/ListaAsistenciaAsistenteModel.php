<?php

namespace App\Models;

use CodeIgniter\Model;

class ListaAsistenciaAsistenteModel extends Model
{
    protected $table = 'tbl_lista_asistencia_asistente';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_lista_asistencia',
        'nombre_completo', 'tipo_documento', 'numero_documento',
        'cargo', 'area_dependencia', 'email', 'celular',
        'token_firma', 'token_expiracion', 'firma_path', 'firmado_at',
        'orden', 'created_at',
    ];
    protected $useTimestamps = false;

    public function getByLista(int $idLista): array
    {
        return $this->where('id_lista_asistencia', $idLista)
            ->orderBy('orden', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    public function getByToken(string $token): ?array
    {
        return $this->where('token_firma', $token)->first();
    }

    public function deleteByLista(int $idLista): bool
    {
        return $this->where('id_lista_asistencia', $idLista)->delete();
    }
}
