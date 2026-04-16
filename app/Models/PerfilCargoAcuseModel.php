<?php

namespace App\Models;

use CodeIgniter\Model;

class PerfilCargoAcuseModel extends Model
{
    protected $table = 'tbl_perfil_cargo_acuse';
    protected $primaryKey = 'id_acuse';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'id_perfil_cargo',
        'id_version',
        'id_trabajador',
        'nombre_trabajador',
        'cedula_trabajador',
        'cargo_trabajador',
        'email_trabajador',
        'estado',
        'token_firma',
        'fecha_envio',
        'fecha_firma',
        'firma_imagen',
        'ip_firma',
        'user_agent',
        'pdf_acuse',
    ];

    public function porPerfil(int $idPerfilCargo): array
    {
        return $this->where('id_perfil_cargo', $idPerfilCargo)
                    ->orderBy('id_acuse', 'DESC')
                    ->findAll();
    }

    public function porToken(string $token): ?array
    {
        $row = $this->where('token_firma', $token)->first();
        return $row ?: null;
    }

    public function existe(int $idPerfilCargo, ?int $idVersion, int $idTrabajador): bool
    {
        $q = $this->where('id_perfil_cargo', $idPerfilCargo)
                  ->where('id_trabajador', $idTrabajador);
        if ($idVersion !== null) $q = $q->where('id_version', $idVersion);
        else $q = $q->where('id_version IS NULL');
        return $q->countAllResults() > 0;
    }

    public function generarLote(int $idPerfilCargo, ?int $idVersion, array $trabajadores, string $cargoDesc = ''): array
    {
        $creados = 0; $saltados = 0;
        foreach ($trabajadores as $t) {
            $idTrab = (int)($t['id_trabajador'] ?? 0);
            if ($idTrab <= 0) continue;
            if ($this->existe($idPerfilCargo, $idVersion, $idTrab)) {
                $saltados++;
                continue;
            }
            $this->insert([
                'id_perfil_cargo'   => $idPerfilCargo,
                'id_version'        => $idVersion,
                'id_trabajador'     => $idTrab,
                'nombre_trabajador' => trim(($t['nombres'] ?? '') . ' ' . ($t['apellidos'] ?? '')),
                'cedula_trabajador' => $t['cedula'] ?? '',
                'cargo_trabajador'  => $cargoDesc,
                'email_trabajador'  => $t['email'] ?? null,
                'estado'            => 'pendiente',
                'token_firma'       => bin2hex(random_bytes(24)),
            ]);
            $creados++;
        }
        return ['creados' => $creados, 'saltados' => $saltados];
    }

    public function marcarFirmado(int $idAcuse, string $firmaBase64, string $ip, string $userAgent): void
    {
        $this->update($idAcuse, [
            'estado'        => 'firmado',
            'firma_imagen'  => $firmaBase64,
            'fecha_firma'   => date('Y-m-d H:i:s'),
            'ip_firma'      => $ip,
            'user_agent'    => substr($userAgent, 0, 255),
        ]);
    }

    public function contarPorPerfil(int $idPerfilCargo): array
    {
        $filas = $this->select('estado, COUNT(*) as total')
                      ->where('id_perfil_cargo', $idPerfilCargo)
                      ->groupBy('estado')
                      ->findAll();
        $out = ['pendiente' => 0, 'enviado' => 0, 'firmado' => 0, 'rechazado' => 0, 'total' => 0];
        foreach ($filas as $f) {
            $out[$f['estado']] = (int)$f['total'];
            $out['total'] += (int)$f['total'];
        }
        return $out;
    }
}
