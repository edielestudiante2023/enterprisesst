<?php

namespace App\Models;

use CodeIgniter\Model;

class ActaTokenModel extends Model
{
    protected $table = 'tbl_actas_tokens';
    protected $primaryKey = 'id_token';
    protected $returnType = 'array';

    protected $allowedFields = [
        'token',
        'tipo',
        'id_acta',
        'id_compromiso',
        'id_miembro',
        'id_asistente',
        'id_cliente',
        'email',
        'nombre',
        'expires_at',
        'usado_at',
        'ip_uso',
        'intentos_uso',
        'max_usos',
        'usos_actuales'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = '';

    /**
     * Generar token único
     */
    public function generarToken(array $data, int $diasExpiracion = 7): string
    {
        $token = bin2hex(random_bytes(32));

        $data['token'] = $token;
        $data['expires_at'] = date('Y-m-d H:i:s', strtotime("+{$diasExpiracion} days"));

        $this->insert($data);

        return $token;
    }

    /**
     * Validar token
     */
    public function validarToken(string $token): ?array
    {
        $registro = $this->where('token', $token)->first();

        if (!$registro) {
            return null;
        }

        // Verificar expiración
        if ($registro['expires_at'] < date('Y-m-d H:i:s')) {
            return null;
        }

        // Verificar usos
        if ($registro['max_usos'] > 0 && $registro['usos_actuales'] >= $registro['max_usos']) {
            return null;
        }

        return $registro;
    }

    /**
     * Marcar token como usado
     */
    public function marcarUsado(string $token): bool
    {
        $request = \Config\Services::request();

        return $this->where('token', $token)
                    ->set([
                        'usado_at' => date('Y-m-d H:i:s'),
                        'ip_uso' => $request->getIPAddress(),
                        'usos_actuales' => 'usos_actuales + 1'
                    ], '', false)
                    ->update();
    }

    /**
     * Registrar intento fallido
     */
    public function registrarIntentoFallido(string $token): bool
    {
        return $this->where('token', $token)
                    ->set('intentos_uso', 'intentos_uso + 1', false)
                    ->update();
    }

    /**
     * Generar token para firma de acta
     */
    public function generarTokenFirma(int $idActa, int $idAsistente, string $email, string $nombre, int $idCliente): string
    {
        return $this->generarToken([
            'tipo' => 'firmar_acta',
            'id_acta' => $idActa,
            'id_asistente' => $idAsistente,
            'id_cliente' => $idCliente,
            'email' => $email,
            'nombre' => $nombre,
            'max_usos' => 1
        ], 7);
    }

    /**
     * Generar token para actualizar tarea
     */
    public function generarTokenTarea(int $idCompromiso, string $email, string $nombre, int $idCliente): string
    {
        return $this->generarToken([
            'tipo' => 'actualizar_tarea',
            'id_compromiso' => $idCompromiso,
            'id_cliente' => $idCliente,
            'email' => $email,
            'nombre' => $nombre,
            'max_usos' => 0 // Ilimitado
        ], 30);
    }

    /**
     * Generar token de acceso para miembro
     */
    public function generarTokenAccesoMiembro(int $idMiembro, string $email, string $nombre, int $idCliente): string
    {
        // Invalidar tokens anteriores del mismo tipo para este miembro
        $this->where('tipo', 'acceso_miembro')
             ->where('id_miembro', $idMiembro)
             ->where('usado_at IS NULL')
             ->delete();

        return $this->generarToken([
            'tipo' => 'acceso_miembro',
            'id_miembro' => $idMiembro,
            'id_cliente' => $idCliente,
            'email' => $email,
            'nombre' => $nombre,
            'max_usos' => 0 // Ilimitado mientras no expire
        ], 30);
    }

    /**
     * Limpiar tokens expirados
     */
    public function limpiarExpirados(): int
    {
        return $this->where('expires_at <', date('Y-m-d H:i:s'))
                    ->delete();
    }

    /**
     * Obtener tokens activos por email
     */
    public function getActivosPorEmail(string $email): array
    {
        return $this->where('email', $email)
                    ->where('expires_at >', date('Y-m-d H:i:s'))
                    ->where('usado_at IS NULL')
                    ->findAll();
    }
}
