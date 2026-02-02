<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'tbl_usuarios';
    protected $primaryKey       = 'id_usuario';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'email',
        'password',
        'nombre_completo',
        'tipo_usuario',
        'id_entidad',
        'estado',
        'ultimo_login',
        'intentos_fallidos',
        'fecha_bloqueo',
        'token_recuperacion',
        'token_expira',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'email'           => 'required|valid_email|is_unique[tbl_usuarios.email,id_usuario,{id_usuario}]',
        'nombre_completo' => 'required|min_length[3]|max_length[255]',
        'tipo_usuario'    => 'required|in_list[admin,consultant,client,miembro]',
        'estado'          => 'required|in_list[activo,inactivo,pendiente,bloqueado]',
    ];

    protected $validationMessages = [
        'email' => [
            'required'    => 'El email es obligatorio.',
            'valid_email' => 'Debe ingresar un email válido.',
            'is_unique'   => 'Este email ya está registrado.',
        ],
        'nombre_completo' => [
            'required'   => 'El nombre es obligatorio.',
            'min_length' => 'El nombre debe tener al menos 3 caracteres.',
        ],
    ];

    protected $skipValidation = false;

    /**
     * Buscar usuario por email
     */
    public function findByEmail(string $email): ?array
    {
        return $this->where('email', $email)->first();
    }

    /**
     * Verificar credenciales de login
     */
    public function verifyCredentials(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return null;
    }

    /**
     * Obtener usuarios con su información de entidad (cliente o consultor)
     */
    public function getUsersWithEntity(): array
    {
        $users = $this->findAll();
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        foreach ($users as &$user) {
            if ($user['tipo_usuario'] === 'client' && $user['id_entidad']) {
                $user['entidad'] = $clientModel->find($user['id_entidad']);
            } elseif (in_array($user['tipo_usuario'], ['consultant', 'admin']) && $user['id_entidad']) {
                $user['entidad'] = $consultantModel->find($user['id_entidad']);
            } else {
                $user['entidad'] = null;
            }
        }

        return $users;
    }

    /**
     * Registrar intento fallido de login
     */
    public function registerFailedAttempt(int $userId): void
    {
        $user = $this->find($userId);
        $intentos = ($user['intentos_fallidos'] ?? 0) + 1;

        $data = ['intentos_fallidos' => $intentos];

        // Bloquear después de 5 intentos fallidos
        if ($intentos >= 5) {
            $data['estado'] = 'bloqueado';
            $data['fecha_bloqueo'] = date('Y-m-d H:i:s');
        }

        $this->update($userId, $data);
    }

    /**
     * Resetear intentos fallidos después de login exitoso
     */
    public function resetFailedAttempts(int $userId): void
    {
        $this->update($userId, [
            'intentos_fallidos' => 0,
            'fecha_bloqueo'     => null,
            'ultimo_login'      => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Actualizar último login
     */
    public function updateLastLogin(int $userId): void
    {
        $this->update($userId, [
            'ultimo_login' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Obtener usuarios por tipo
     */
    public function getByType(string $tipo): array
    {
        return $this->where('tipo_usuario', $tipo)->findAll();
    }

    /**
     * Obtener usuarios activos
     */
    public function getActiveUsers(): array
    {
        return $this->where('estado', 'activo')->findAll();
    }

    /**
     * Hashear password
     */
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * Crear usuario con password hasheado
     * @return int|false ID del usuario creado o false si falla
     */
    public function createUser(array $data): int|false
    {
        if (isset($data['password'])) {
            $data['password'] = $this->hashPassword($data['password']);
        }

        $result = $this->insert($data);

        // Si falla, registrar el error para debugging
        if ($result === false) {
            log_message('error', 'Error al crear usuario: ' . json_encode($this->errors()));
            log_message('error', 'Datos enviados: ' . json_encode(array_diff_key($data, ['password' => ''])));
        }

        return $result;
    }

    /**
     * Obtener errores de la última operación
     */
    public function getLastErrors(): array
    {
        return $this->errors();
    }

    /**
     * Actualizar usuario (hashear password si se proporciona)
     */
    public function updateUser(int $id, array $data): bool
    {
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = $this->hashPassword($data['password']);
        } else {
            unset($data['password']);
        }

        return $this->update($id, $data);
    }
}
