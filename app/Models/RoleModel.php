<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table            = 'tbl_roles';
    protected $primaryKey       = 'id_rol';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nombre_rol',
        'descripcion',
        'permisos',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Obtener rol por nombre
     */
    public function findByName(string $nombre): ?array
    {
        return $this->where('nombre_rol', $nombre)->first();
    }

    /**
     * Obtener roles de un usuario
     */
    public function getUserRoles(int $userId): array
    {
        return $this->db->table('tbl_usuario_roles ur')
            ->select('r.*')
            ->join('tbl_roles r', 'r.id_rol = ur.id_rol')
            ->where('ur.id_usuario', $userId)
            ->get()
            ->getResultArray();
    }

    /**
     * Asignar rol a usuario
     */
    public function assignRoleToUser(int $userId, int $roleId): bool
    {
        return $this->db->table('tbl_usuario_roles')->insert([
            'id_usuario' => $userId,
            'id_rol'     => $roleId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Remover rol de usuario
     */
    public function removeRoleFromUser(int $userId, int $roleId): bool
    {
        return $this->db->table('tbl_usuario_roles')
            ->where('id_usuario', $userId)
            ->where('id_rol', $roleId)
            ->delete();
    }

    /**
     * Verificar si usuario tiene un rol específico
     */
    public function userHasRole(int $userId, string $roleName): bool
    {
        $result = $this->db->table('tbl_usuario_roles ur')
            ->join('tbl_roles r', 'r.id_rol = ur.id_rol')
            ->where('ur.id_usuario', $userId)
            ->where('r.nombre_rol', $roleName)
            ->countAllResults();

        return $result > 0;
    }
}
