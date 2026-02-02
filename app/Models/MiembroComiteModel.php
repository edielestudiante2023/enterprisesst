<?php

namespace App\Models;

use CodeIgniter\Model;

class MiembroComiteModel extends Model
{
    protected $table = 'tbl_comite_miembros';
    protected $primaryKey = 'id_miembro';
    protected $returnType = 'array';

    protected $allowedFields = [
        'id_comite',
        'id_cliente',
        'id_responsable', // FK a tbl_cliente_responsables_sst
        'nombre_completo',
        'tipo_documento',
        'numero_documento',
        'cargo',
        'area_dependencia',
        'email',
        'telefono',
        'representacion',
        'tipo_miembro',
        'rol_comite',
        'puede_crear_actas',
        'puede_cerrar_actas',
        'fecha_ingreso',
        'fecha_retiro',
        'motivo_retiro',
        'firma_imagen',
        'estado'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Obtener miembros activos de un comité
     */
    public function getActivosPorComite(int $idComite): array
    {
        return $this->where('id_comite', $idComite)
                    ->where('estado', 'activo')
                    ->orderBy('tipo_miembro', 'ASC') // Principales primero
                    ->orderBy('rol_comite', 'ASC')   // Presidente, Secretario, Miembro
                    ->findAll();
    }

    /**
     * Obtener miembros por tipo (principal/suplente)
     */
    public function getActivosPorTipo(int $idComite, string $tipoMiembro): array
    {
        return $this->where('id_comite', $idComite)
                    ->where('estado', 'activo')
                    ->where('tipo_miembro', $tipoMiembro)
                    ->findAll();
    }

    /**
     * Contar miembros activos
     */
    public function contarActivos(int $idComite): int
    {
        return $this->where('id_comite', $idComite)
                    ->where('estado', 'activo')
                    ->countAllResults();
    }

    /**
     * Contar por tipo (principal/suplente)
     */
    public function contarPorTipo(int $idComite, string $tipoMiembro): int
    {
        return $this->where('id_comite', $idComite)
                    ->where('estado', 'activo')
                    ->where('tipo_miembro', $tipoMiembro)
                    ->countAllResults();
    }

    /**
     * Obtener presidente del comité
     */
    public function getPresidente(int $idComite): ?array
    {
        return $this->where('id_comite', $idComite)
                    ->where('estado', 'activo')
                    ->where('rol_comite', 'presidente')
                    ->first();
    }

    /**
     * Obtener secretario del comité
     */
    public function getSecretario(int $idComite): ?array
    {
        return $this->where('id_comite', $idComite)
                    ->where('estado', 'activo')
                    ->where('rol_comite', 'secretario')
                    ->first();
    }

    /**
     * Obtener miembros que pueden crear actas
     */
    public function getPuedenCrearActas(int $idComite): array
    {
        return $this->where('id_comite', $idComite)
                    ->where('estado', 'activo')
                    ->where('puede_crear_actas', 1)
                    ->findAll();
    }

    /**
     * Obtener miembros que pueden cerrar actas
     */
    public function getPuedenCerrarActas(int $idComite): array
    {
        return $this->where('id_comite', $idComite)
                    ->where('estado', 'activo')
                    ->where('puede_cerrar_actas', 1)
                    ->findAll();
    }

    /**
     * Buscar miembro por email en un cliente
     */
    public function getByEmailYCliente(string $email, int $idCliente): ?array
    {
        return $this->where('email', $email)
                    ->where('id_cliente', $idCliente)
                    ->where('estado', 'activo')
                    ->first();
    }

    /**
     * Obtener comités a los que pertenece un miembro (por email)
     */
    public function getComitesPorEmail(string $email, int $idCliente): array
    {
        return $this->select('tbl_comite_miembros.*, tbl_comites.id_comite, tbl_tipos_comite.codigo, tbl_tipos_comite.nombre as tipo_nombre')
                    ->join('tbl_comites', 'tbl_comites.id_comite = tbl_comite_miembros.id_comite')
                    ->join('tbl_tipos_comite', 'tbl_tipos_comite.id_tipo = tbl_comites.id_tipo')
                    ->where('tbl_comite_miembros.email', $email)
                    ->where('tbl_comite_miembros.id_cliente', $idCliente)
                    ->where('tbl_comite_miembros.estado', 'activo')
                    ->where('tbl_comites.estado', 'activo')
                    ->findAll();
    }

    /**
     * Retirar miembro (solo consultor puede hacer esto)
     */
    public function retirarMiembro(int $idMiembro, string $motivo): bool
    {
        return $this->update($idMiembro, [
            'estado' => 'retirado',
            'fecha_retiro' => date('Y-m-d'),
            'motivo_retiro' => $motivo
        ]);
    }

    /**
     * Verificar si tiene quórum (mitad + 1 de principales)
     */
    public function calcularQuorumRequerido(int $idComite): int
    {
        $principales = $this->contarPorTipo($idComite, 'principal');
        return (int) floor($principales / 2) + 1;
    }

    /**
     * Obtener miembros por representación (empleador/trabajador)
     */
    public function getByRepresentacion(int $idComite, string $representacion): array
    {
        return $this->where('id_comite', $idComite)
                    ->where('estado', 'activo')
                    ->where('representacion', $representacion)
                    ->findAll();
    }

    /**
     * Verificar paridad (mismo número empleador/trabajador)
     */
    public function verificarParidad(int $idComite): array
    {
        $empleador = count($this->getByRepresentacion($idComite, 'empleador'));
        $trabajador = count($this->getByRepresentacion($idComite, 'trabajador'));

        return [
            'empleador' => $empleador,
            'trabajador' => $trabajador,
            'hay_paridad' => $empleador === $trabajador,
            'diferencia' => abs($empleador - $trabajador)
        ];
    }
}
