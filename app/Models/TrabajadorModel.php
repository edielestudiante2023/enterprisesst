<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\Traits\TenantScopedModel;

class TrabajadorModel extends Model
{
    use TenantScopedModel;

    protected $table = 'tbl_trabajadores';
    protected $primaryKey = 'id_trabajador';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'id_cliente',
        'id_cargo_cliente',
        'nombres',
        'apellidos',
        'tipo_documento',
        'cedula',
        'email',
        'telefono',
        'fecha_ingreso',
        'fecha_retiro',
        'activo',
    ];

    protected $validationRules = [
        'id_cliente'     => 'required|integer',
        'nombres'        => 'required|max_length[150]',
        'apellidos'      => 'required|max_length[150]',
        'tipo_documento' => 'required|in_list[CC,CE,PA,TI,PEP]',
        'cedula'         => 'required|max_length[30]',
    ];

    public function porCliente(int $idCliente, bool $soloActivos = true): array
    {
        $q = $this->where('id_cliente', $idCliente);
        if ($soloActivos) $q = $q->where('activo', 1);
        return $q->orderBy('apellidos', 'ASC')->orderBy('nombres', 'ASC')->findAll();
    }

    public function porCargo(int $idCargoCliente, bool $soloActivos = true): array
    {
        $q = $this->where('id_cargo_cliente', $idCargoCliente);
        if ($soloActivos) $q = $q->where('activo', 1);
        return $q->orderBy('apellidos', 'ASC')->findAll();
    }

    public function buscarPorCedula(int $idCliente, string $cedula): ?array
    {
        $row = $this->where('id_cliente', $idCliente)
                    ->where('cedula', $cedula)
                    ->first();
        return $row ?: null;
    }

    public function contarPorCargo(int $idCargoCliente): int
    {
        return $this->where('id_cargo_cliente', $idCargoCliente)
                    ->where('activo', 1)
                    ->countAllResults();
    }
}
